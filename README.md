# UserForms - SearchField

## Overview

This is a custom [https://github.com/silverstripe/silverstripe-userforms](silverstripe-userforms)
form field that can be added to provide a field that searches for FAQ articles that already exist
within the website.

The use case for this field is where an enquiry form exists on the website for users to ask a
question. It can be used to indicate that a similar question already has an FAQ article to answer
it before they submit their enquiry.

## Installation

This can be installed via:

`composer require benmanu/silverstripe-userforms-faqsearchfield`

## Javascript Development

This module has [https://github.com/JeffreyWay/laravel-mix](laravel-mix) as an npm
dependency which makes it easy to do future javascript development for the module.

To get everything setup you just need to run:

`yarn install`

Once all the npm packages have been installed you can start javascript development
using either:

`yarn run dev`: which will start a watch task that rebundles the src files when files
in the `javascript/src/` folder are updated.

`yarn run prod`: which you will want to run when finished to produce a production ready
build of the javascript src files.

## TODO:

* Add ajax loader icon to indicate request is being made on field blur
