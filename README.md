# No Comment

A plugin to close, disable and remove comments from the WordPress admin UI.

### What this plugin does:
- Sets previously published comments to _unapproved_ (=pending).
- Closes comments and pings on all published posts of all post types once. (Future status handled by global default status.)
- Sets global default comment and ping status to closed.
- Removes comments and discussion menu pages from admin menu.
- Removes the comment menu item from admin bar.
- Removes post type support for comments and trackbacks from all registered post types.
- Removes the default comments widget.
- Removes comments from the Dashboard by cloning the Activity widget. ([before](https://github.com/glueckpress/no-comment/blob/master/assets/img/screenshot-2.png)/[after](https://github.com/glueckpress/no-comment/blob/master/assets/img/screenshot-3.png))
- Removes comment feed link from wp_head.

### Requirements:
- PHP 5.3+
- WordPress 4.1+

are 2 major operations that may come costly on sites with a lot of posts or comments

### License
- [General Public License v3](http://www.gnu.org/licenses/gpl-3.0.html)
