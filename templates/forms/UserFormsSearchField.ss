<div class="js-searchfield">
  <input $AttributesHTML<% if $RightTitle %>aria-describedby="{$Name}_right_title" <% end_if %> @blur="onBlur" />
  <div v-if="suggestions.length" class="form-control suggestions">
    <h1 class="suggestions__heading">Do any of these articles answer your question?</h1>
    <div v-for="suggestion in suggestions">
      <a :href="suggestion.Link" class="suggestions__question" target="_blank">{{ suggestion.Question }}</a>
      <p class="suggestions__answer">{{ suggestion.Answer }}</p>
    </div>
  </div>
</div>
