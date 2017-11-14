[![Latest Stable Version](https://poser.pugx.org/thecodingmachine/cms-static-registry/v/stable)](https://packagist.org/packages/thecodingmachine/cms-static-registry)
[![Total Downloads](https://poser.pugx.org/thecodingmachine/cms-static-registry/downloads)](https://packagist.org/packages/thecodingmachine/cms-static-registry)
[![Latest Unstable Version](https://poser.pugx.org/thecodingmachine/cms-static-registry/v/unstable)](https://packagist.org/packages/thecodingmachine/cms-static-registry)
[![License](https://poser.pugx.org/thecodingmachine/cms-static-registry/license)](https://packagist.org/packages/thecodingmachine/cms-static-registry)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/cms-static-registry/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thecodingmachine/cms-static-registry/?branch=master)
[![Build Status](https://travis-ci.org/thecodingmachine/cms-static-registry.svg?branch=master)](https://travis-ci.org/thecodingmachine/cms-static-registry)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/cms-static-registry/badge.svg?branch=master&service=github)](https://coveralls.io/github/thecodingmachine/cms-static-registry?branch=master)


CMS static registry
===================

Load pages/blocks/themes from static files in your repository.

This package can ban used with the [CMS interfaces](https://github.com/thecodingmachine/cms-interfaces) to build a full-featured PSR-7 CMS.

Why?
----

Most CMSes out there store content of HTML pages in databases. While there are a number of advantages to do so, this also comes with some drawbacks:

This package is a "file store". It proposes to store files, blocks, themes, etc... into static files instead.

Files have a number of advantages over databases:

- They can be committed in your code repository
- Therefore, it is easy to migrate content from a test environment to a production environment (content is part of your code)
- It is also easy to keep track of history (using your favorite VCS like GIT)
- You can easily work as a team on some content and use branching and merging capability of your VCS to manage content

Of course, this is no silver bullet and using a database to store content can make a great deal of sense.
But for content that is mostly administered by a technical team, storing content in files instead of a database is a breeze of fresh air.


Directory structure
-------------------

Your website will typically be stored in directory of your project.

The default proposed directory structure is:

- cms_root
    - pages
        - a_page.html
        - another_page.md
    - blocks
        - a_block.html
        - another_block.md
    - themes
        - my_theme
            - index.twig
            - config.yml
            - css/
            - js/
            - ...
    - sub_themes
        - a_subtheme.yml
        - another_subtheme.yml
        

### Pages

A page is... well... it's a page of your website!
Pages can be:

- in HTML (if the extension is `.html`)
- in Markdown (if the extension is `.md`)

Pages come with a *YAML frontmatter*.

Here is a sample page:

```html
---
url: hello/world
website : example.com
lang : fr
title : foo
theme : foo_theme
meta_title : bar
meta_description : baz
menu : menu 1 / menu 2 / menu 3
menu_order : 1
---

<h1>Hello world!</h1>
```

The YAML frontmatter MUST be surrounded by `---`.

Parameters of the YAML Frontmatter:

Name      | Compulsory | Description
----------|------------|------------------------
url       | *Yes*      | The URL of the page. It contains only the *path*. For instance: `/foo/bar` 
website   | *No*       | The domain name of the page. For instance: *example.com*
lang      | *Yes*      | The language of the page, on 2 characters. For instance: "en", "fr"...
title     | *Yes*      | The title of the page (goes into the &lt;title> HTML tag
theme     | *No*       | The theme (or sub-theme) of the page (more about themes below)

TODO: continue documentation, migrate menu into an array, with inlined order. 
