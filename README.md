SEO URLs for Concrete5
============

This Concrete5 add-on gives you total control over your page URLs by not including parent pages.

This is accomplished by overriding the core page model to prevent parent page paths from being included in a page's url.

This allows the user to have full control over a page's url.  After installing this package, whenever
a page is renamed, it and all of its children will have their parent pages' urls removed from their url-slug.
Old urls will be lost and not be added to the alias list, so if you want to maintain old links, be sure to get a
list of old urls and redirect as needed.

This add-on specifically ignores and does not effect dashboard pages since they must have have 'dashboard' in
their url in order to work properly.

