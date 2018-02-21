<?php

class UserDefinedForm_ControllerFAQExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'view',
        'searchFieldAction'
    ];

    /**
     * Sets the character limit for the 'answer' data
     *
     * @var integer
     */
    private static $answer_character_limit = 120;

    /**
     * Solr index used to search Knowledgebase articles
     *
     * @var string
     */
    public static $search_index_class = 'KnowledgebaseSearchIndex';

    /**
     * Array which defines classes/models for Solr to search in
     *
     * @var array
     */
    public static $classes_to_search = [
        [
            'class' => 'FAQ',
            'includeSubclasses' => true
        ]
    ];

    /**
     * Action for triggering the search behaviour of the search field. Formats
     * the response based on the Solr data received.
     *
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse
     */
    public function searchFieldAction(SS_HTTPRequest $request)
    {
        // decode JSON data and extract keywords
        $data = json_decode(html_entity_decode($request->getBody(), ENT_QUOTES), true);
        $keywords = $data['keyword'];

        // execute the search based on keywords
        $renderData = $this->search($keywords);

        // format the response with Solr data
        $this->owner->getResponse()->setStatusCode(200);
        $this->owner->getResponse()->setBody(json_encode($renderData));

        return $this->owner->getResponse();
    }

    /**
     * Executes the search function based on the keywords entered.
     *
     * @param string $keywords
     *
     * @return array Data to be rendered
     */
    public function search($keywords)
    {
        // Set starting and limit parameters
        $start = 0;
        $limit = 3;

        $renderData = [];

        // if no query is entered return empty results
        if (!$keywords) {
            return $renderData;
        }

        // build search query based on keyword
        $query = $this->getSearchQuery($keywords);

        try {
            // execute search
            $searchResult = $this->doSearch($query, $start, $limit);

            // parse the search results so its pure FAQ data - question, answer and link
            $renderData = $this->parseSearchResults($searchResult->Matches);

        } catch(Exception $e) {

            $renderData = [
                'SearchError' => true
            ];

            SS_Log::log($e, SS_Log::WARN);
        }

        return $renderData;
    }

    /**
     * Construct the search query required for Solr
     *
     * @param string $keywords
     *
     * @return SearchQuery
     */
    public function getSearchQuery($keywords)
    {
        $query = new SearchQuery();
        $query->classes = self::$classes_to_search;
        $query->search($keywords);

        // Artificially lower the amount of results to prevent too high resource usage.
        // on subsequent canView check loop.
        $query->limit(100);

        return $query;
    }

    /**
     * Performs a search against the configured Solr index from a given query, start and limit
     *
     * @param SearchQuery $query
     * @param int $start
     * @param int $limit
     *
     * @return ArrayData
     */
    public function doSearch($query, $start, $limit)
    {
        $result = singleton(self::$search_index_class)->search(
            $query,
            $start,
            $limit,
            [
                'defType' => 'edismax',
                'hl' => 'true',
                'spellcheck' => 'true',
                'spellcheck.collate' => 'true'
            ]
        );

        return $result;
    }

    /**
     * Formatting the suggestions data into an array format so that it can be
     * encoded into JSON
     *
     * @param PaginatedList $results
     *
     * @return array of suggested results
     */
    public function parseSearchResults($results)
    {
        $renderData = [];

        $originalFilterValue = Subsite::$disable_subsite_filter;
        Subsite::$disable_subsite_filter = true;

        $page = KnowledgebasePage::get()->first();

        Subsite::$disable_subsite_filter = $originalFilterValue;

        if (!$page) {
            return $renderData;
        }

        // extract FAQ data for each result
        foreach ($results as $result) {

            if (!$result->canView()) {
                $results->remove($result);
            }

            // limit answer field to 120 characters
            $answer = HTMLText::create('Answer');
            $answer->setValue($result->Answer);
            $answer->NoHTML();
            $answer = $answer->LimitCharacters(Config::inst()->get('UserDefinedForm_ControllerFAQExtension', 'answer_character_limit'));

            // construct renderData with question, answer, link
            $renderData[] = [
                'Question' => $result->Question,
                'Answer' => $answer,
                'Link' => sprintf('%s%s/view/%d', $page->AbsoluteLink(), $page->URLSegment, $result->ID)
            ];
        }

        return $renderData;
    }

}
