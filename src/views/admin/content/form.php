<header id="content-header">
    <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">

<form id="content_form" name="content_form" action="index.php" method="post">

<?php
$is_new = ((int) $content->id() <= 0);
$is_post = (get_class($content->parent()) === "PostsCollectionContent");
if ($is_new && !$is_post) :
?>
<p>
    <label for="type" class="inline">Type:</label>
    <select name="type" id="type">
        <option value="PageContent">Page</option>
        <option value="PostsCollectionContent">Post Collection (blog)</option>
        <option value="FeedContent">RSS/Atom Feed</option>
        <option value="FeedAggregatorContent">RSS/Atom Feed Aggregator</option>
        <?php if (!$session->isAdmin()) : ?>
        <option value="MVCContent">MVC action</option>
        <?php endif; ?>
    </select>
</p>
<?php else : ?>
    <input
        type="hidden"
        name="type"
        value="<?php echo $content->type(); ?>"
        id="type"
    />
<?php endif; ?>

<p>
    <label for="slug" class="inline">URL:</label>
    <?php
    echo $base_url;
    $parent = $content->parent();
    if (!$content->slug()
        && $parent instanceof Content
        && $parent->parentId() > 0
    ) {
        $content->slug($parent->slug()."/");
        echo "<input type=\"hidden\" name=\"parent_slug\" id=\"parent-slug\"";
        echo " value=\"".$parent->slug()."\" />";
    }
    ?>
    <input
        type="text"
        name="slug"
        value="<?php echo $content->slug(); ?>"
        id="slug"
        maxlength="255"
    />

    <?php if ($content->id()): ?>
        <a href="<?php echo $content->slug(); ?>" target="_blank">Preview</a>
    <?php endif ?>
</p>

<p>
    <label for="title">Title:</label>
    <input
        type="text"
        name="title"
        id="title"
        value="<?php echo $content->title(); ?>"
        class="required"
        maxlength="255"
    />
</p>

<p>
    <label for="short_title">Short title (Optional):</label>
    <input
        type="text"
        name="short_title"
        id="short_title"
        value="<?php echo $content->shortTitle(); ?>"
        maxlength="50"
    />
</p>

<p class="typeparam page post">
    <label for="body">Body:</label>

    <a href="#" id="tinymce-button-visual">[ Visual ]</a>
    <a href="#" id="tinymce-button-html">[ HTML ]</a>

    <textarea
        name="body"
        id="body"
        rows="25"
        cols="81"
        class="tinymce"
    ><?php echo $content->body(); ?></textarea>
</p>

<p class="typeparam page posts_collection">
    <label for="view" class="inline">Custom template:</label>
    <?php echo $helper->selectCustomView($content->param("view")); ?>
</p>

<?php
if ($content instanceof PostsCollectionContent
    || $content instanceof PageContent) :
?>
<p class="typeparam posts_collection">
    <label for="posts_per_page" class="inline">Posts per page:</label>
    <input
        type="text"
        name="posts_per_page"
        id="posts_per_page"
        size="2"
        maxlength="3"
        value="<?php if ($content instanceof PostsCollectionContent) echo $content->param("posts_per_page"); ?>"
    />
</p>
<?php endif; ?>

<?php
if ($content instanceof FeedContent
    || $content instanceof PageContent) :
?>
<p class="typeparam feed">
    <label for="feed_url" class="inline">Feed URL:</label>
    <input
        type="text"
        name="feed_url"
        id="feed_url"
        size="50"
        maxlength="255"
        value="<?php if ($content instanceof FeedContent) echo $content->param("feed_url"); ?>"
    />
</p>

<p class="typeparam feed">
    <label for="cache_time" class="inline">Cache time:</label>
    <input
        type="text"
        name="cache_time"
        id="cache_time"
        size="4"
        maxlength="6"
        value="<?php if ($content instanceof FeedContent) echo $content->param("cache_time"); ?>"
    /> in seconds
</p>
<?php endif; ?>

<fieldset id="publishing" class="publishing">
    <legend>Publishing</legend>

    <p>
        <?php $array    =  explode(" ", $content->pubDate()); ?>
        <?php $pub_date =  $array[0]; ?>
        <?php $pub_time =  explode(":", $array[1]); ?>
        <label for="pub_date" class="inline">Publish date:</label>
        <?php echo PHPFrame_HTMLUI::calendar("pub_date", "pub_date", $pub_date); ?>
        @
        <input
            type="text"
            name="pub_time_h"
            id="pub_time_h"
            value="<?php echo $pub_time[0]; ?>"
            size="2"
            maxlength="2"
        />:
        <input
            type="text"
            name="pub_time_m"
            id="pub_time_m"
            value="<?php echo $pub_time[1]; ?>"
            size="2"
            maxlength="2"
        />
    </p>

    <p>
        <label for="status" class="inline">Status:</label>
        <select name="status" id="status">
            <option value="0" <?php if ($content->status() == 0) echo "selected"; ?>>
                Draft
            </option>
            <option value="1" <?php if ($content->status() == 1) echo "selected"; ?>>
                Published
            </option>
            <option value="2" <?php if ($content->status() == 2) echo "selected"; ?>>
                Archived
            </option>
        </select>
    </p>
</fieldset>

<fieldset id="metadata" class="metadata">
    <legend>Meta data</legend>

    <p>
        <label for="description">Description:</label>
        <textarea
            name="description"
            id="description"
            rows="3"
            cols="80"
        ><?php echo $content->description(); ?></textarea>
    </p>

    <p>
        <label for="keywords">Keywords:</label>
        <textarea
            name="keywords"
            id="keywords"
            rows="3"
            cols="80"
        ><?php echo $content->keywords(); ?></textarea>
    </p>

    <p>
        <label for="robots_index" class="inline">
            Allow robots to index this document?
        </label>
        <input
            type="checkbox"
            name="robots_index"
            id="robots_index"
            <?php if ($content->robotsIndex()) echo "checked=\"checked\""; ?>
        />
        <br />
        <label for="robots_follow" class="inline">
            Allow robots to follow links in this document?
        </label>
        <input
            type="checkbox"
            name="robots_follow"
            id="robots_follow"
            <?php if ($content->robotsFollow()) echo "checked=\"checked\""; ?>
        />
    </p>
</fieldset>

<?php $perms = (string) $content->perms(); ?>
<fieldset id="permissions" class="permissions">
    <legend>Permissions</legend>

    <p>
        <label for="read">Who can read this document?</label>
        <select name="read" id="read">
            <option value="owner">Only me</option>
            <option value="group" <?php if ($perms[1] == 4 && $perms[2] != 4) echo "selected"; ?>>
                Only members of the group assigned below
            </option>
            <option value="world" <?php if ($perms[2] == 4) echo "selected"; ?>>
                Everyone
            </option>
        </select>
    </p>

    <p>
        <label for="write">Who can edit/delete this document?</label>
        <select name="write" id="write">
            <option value="owner">Only me</option>
            <option value="group" <?php if ($perms[1] == 6 && $perms[2] != 6) echo "selected"; ?>>
                Only members of the group assigned below
            </option>
            <option value="world" <?php if ($perms[2] == 6) echo "selected"; ?>>
                Everyone
            </option>
        </select>
    </p>

    <p>
        <strong>Group access</strong>:
        <?php echo $user_helper->getGroupsPicker($content->group()); ?>
    </p>
</fieldset>

<p>
    <span class="button_wrapper">
        <input
                type="button"
                onclick="window.history.back();"
                value="Back"
                class="button back"
        />
    </span>
    <span class="button_wrapper">
         <button type="reset" class="button reset" >Reset</button>
    </span>
    <?php if ($content->status() < 1) : ?>
    <span class="button_wrapper">
         <input
                 type="submit"
                 class="button"
                 value="Save draft"
                 onclick="document.content_form.status.selectedIndex = 0;"
          />
    </span>
    <span class="button_wrapper">
         <input
                 type="submit"
                 class="button"
                 value="Publish"
                 onclick="document.content_form.status.selectedIndex = 1;"
          />
    </span>
    <?php else: ?>
    <span class="button_wrapper">
         <input
                 type="submit"
                 class="button"
                 value="Save"
          />
    </span>
    <?php endif; ?>

</p>

<input type="hidden" name="id" value="<?php echo $content->id(); ?>" />
<input type="hidden" name="parent_id" value="<?php echo $content->parentId(); ?>" />
<input type="hidden" name="controller" value="content" />
<input type="hidden" name="action" value="save" />
</form>

</div><!-- #content-body -->

<script>jQuery(document).ready(function() { EN.initContentForm(); });</script>

