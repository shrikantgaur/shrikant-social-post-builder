=== Shrikant Social Post Builder ===
Contributors: shrikantgaur
Tags: social share, post compiler, share to whatsapp, auto share, social post
Stable tag: 1.0.0
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generates ready-to-share social media messages from WordPress posts in one click.

== Description ==

Publishers publish multiple posts daily. To share on social networks like WhatsApp, Telegram, Facebook, LinkedIn, X (Twitter), Viber, or Signal, they manually copy titles and links. This plugin streamlines the process down to a few clicks: select posts, arrange drag-and-drop order, choose a formatting template, and generate ready-to-share text compiled instantly! Optimize your social media marketing and automated publishing flow with this lightweight social post generator helper.

### Key Features
* **Multi-Post Selection**: Find posts easily using category, tag, author, publication date, or keyword search.
* **Drag-and-Drop Reordering**: Rearrange the sequence of selected posts to list them exactly as you want.
* **Ready-Made Social Templates**: Built-in template designs for WhatsApp, Telegram, Facebook, LinkedIn, and X.
* **Custom Template Creator**: Create your own dynamic templates with custom formatting tags.
* **Formatting Options**: Auto-number posts list, toggle emojis, append website URL, default footer message, or default hashtags.
* **Instant Clipboard Copy**: Copy generated text in one click to paste anywhere.
* **Audit & Compilation History**: Logs compiled messages in a database with full trace details and quick duplicate options.
* **Retention Cleanup Settings**: Automated tools to clear logs older than X days or limit total history entries.

### Supported Template Placeholders
Build custom templates using these dynamic tag placeholders:
* `{{title}}` - The title of the WordPress post.
* `{{url}}` - The permalink of the post.
* `{{excerpt}}` - The post excerpt or snippet.
* `{{date}}` - The publication date.
* `{{author}}` - The author's display name.
* `{{website}}` - Custom site link defined in plugin settings.
* `{{footer}}` - Default footer message text.
* `{{hashtags}}` - Group of default hashtags.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/shrikant-social-post-builder` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Access the 'Shrikant Social Post Builder' menu from your WordPress admin dashboard.

== How to Use ==

1. **Select Posts**: Go to the 'Create Post' tab. Use the search input or category, tag, author, or custom date range filters to find the posts you want to compile. Check the boxes next to the posts you wish to include.
2. **Arrange Order**: Go to Step 2 ('Arrange Order') and use the drag-and-drop handles to order the posts exactly as you want them to appear in the message.
3. **Choose Template**: In Step 3, choose the platform format template (e.g. WhatsApp, Telegram, Facebook, LinkedIn, X, or your own Custom template).
4. **Configure Options**: Toggle formatting preferences in Step 4, such as prepending list numbers, showing emojis, and appending website URL, default footer, or hashtags.
5. **Generate & Share**: In Step 5, click 'Generate' to compile your message. You can copy the generated text to your clipboard with a single click, and paste it directly into any app or platform.

== Screenshots ==

1. Dashboard metrics card summary.
2. Select WordPress posts for compiling in Step 1.
3. Drag-and-drop posts ordering in Step 2.
4. Choose templates with SVG brand logos in Step 3.
5. Setup formatting options in Step 4.
6. Generate compiled messages and copy to clipboard in Step 5.
7. Managing custom social platform templates.
8. Template editor modal workspace.
9. Audit logs and generated post history.
10. Retention settings and general options configuration.

== Frequently Asked Questions ==

= Does this plugin automatically post to social media channels? =
No. This plugin compiles your chosen posts into a beautifully formatted text block. You can then copy it with one click and paste it manually into your social media channel (e.g. WhatsApp groups, Telegram channels, X threads). This gives you full control and avoids account suspension risks associated with auto-posting APIs.

= How do I create a custom template? =
Go to the 'Templates' tab and click 'Add Custom Template'. You can use any combination of text, emojis, and dynamic placeholders like `{{title}}` or `{{url}}`.

= Can I auto-delete old compilation history? =
Yes. Go to 'Settings' and configure the 'Auto Delete Logs (Days)' and 'Maximum History Records Limit'. The database will clean itself up automatically to save space.

= Which post types are supported? =
Currently, standard WordPress posts (`post`) are fully supported for compilation.

== Changelog ==

= 1.0.0 =
* Initial release.
