<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<h2>Client Apps</h2>

<?php if (count($clients) > 0) : ?>
<ul>
<?php foreach ($clients as $client) : ?>
    <li>
        <p>
            <?php echo $client->name(); ?> <?php echo $client->version(); ?>
            (<?php echo $client->vendor(); ?>)
            <br />
            Status: <?php echo $client->status(); ?><br />
            API Key: <?php echo $client->key(); ?><br />
            API Secret: <?php echo $client->secret(); ?>
        </p>
<?php endforeach; ?>
</ul>
<?php else : ?>
<p>
    No client apps registered with system.
</p>
<?php endif; ?>

</div><!-- .entry -->

<div class="entry">

<h2>API Methods</h2>

<table>
<tr>
    <th>method</th>
    <th>oauth</th>
    <th>cookie</th>
</tr>
<?php foreach ($api_methods as $method) : ?>
<tr>
    <td><?php echo $method["method"]; ?></td>
    <td>
        <a
            class="api-method-link api-method-link-oauth"
            href="<?php echo $method["method"]; ?>"
            title="<?php echo $method["oauth"]; ?>"
        >
        <?php
        if ($method["oauth"] == 1) {
            echo "both";
        } elseif ($method["oauth"] == 2) {
            echo "2 legged";
        } elseif ($method["oauth"] == 3) {
            echo "3 legged";
        } else {
            echo "no";
        }
        ?>
        </a>
    </td>
    <td>
        <a
            class="api-method-link api-method-link-cookie"
            href="<?php echo $method["method"]; ?>"
            title="<?php echo $method["cookie"]; ?>"
        >
        <?php
        if ($method["cookie"] == 1) {
            echo "yes";
        } elseif ($method["cookie"] == 2) {
            echo "enforce token";
        } else {
            echo "no";
        }
        ?>
        </a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<script>
jQuery('.api-method-link').click(function(e) {
    e.preventDefault();

    if (jQuery('#api-method-form').length > 0) {
        jQuery('#api-method-form').prev('a').css('display', 'inline');
        jQuery('#api-method-form').remove();
    }

    jQuery(this).css('display', 'none');


    var selectName;
    if (jQuery(this).hasClass('api-method-link-oauth')) {
        selectName = 'oauth';
    } else {
        selectName = 'cookie';
    }

    var apiOauthSelect = '<form id="api-method-form">';
    apiOauthSelect += '<select name="' + selectName + '" id="';
    apiOauthSelect += selectName + '" onchange="saveOAuthMethod(\'';
    apiOauthSelect += jQuery(this).attr('href');
    apiOauthSelect += '\', this);">';
    apiOauthSelect += '<option value="0">no</option>';
    if (selectName === 'oauth') {
        apiOauthSelect += '<option value="1">both</option>';
        apiOauthSelect += '<option value="2">2 legged</option>';
        apiOauthSelect += '<option value="3">3 legged</option>';
    } else {
        apiOauthSelect += '<option value="1">yes</option>';
        apiOauthSelect += '<option value="2">enforce token</option>';
    }
    apiOauthSelect += '</select>';
    apiOauthSelect += '</form>';

    jQuery(this).after(apiOauthSelect);

    jQuery('#' + selectName).val(jQuery(this).attr('title'));
});

var saveOAuthMethod = function(method, select)
{
    var form = jQuery('#api-method-form');
    var selectedValue = select.options[select.selectedIndex].value;
    var data = {
        method: method,
        suppress_response_codes: 1,
        format: 'json',
        token: '<?php echo $token; ?>'
    };

    if (select.name === 'oauth') {
        data.oauth = selectedValue;
    } else if (select.name === 'cookie') {
        data.cookie = selectedValue;
    }

    jQuery.ajax({
        url: '<?php echo $base_url; ?>api/oauth/save_method_auth',
        data: data,
        type: 'POST',
        success: function (response) {
            if (+response !== 1) {
                alert('Something went wrong when saving API method info');
            }

            var anchorText = '';
            if (selectedValue == 0) {
                anchorText = 'no';
            } else if (selectedValue == 1 && select.name == 'oauth') {
                anchorText = 'both';
            } else if (selectedValue == 1 && select.name == 'cookie') {
                anchorText = 'yes';
            } else if (selectedValue == 2 && select.name == 'oauth') {
                anchorText = '2 legged';
            } else if (selectedValue == 2 && select.name == 'cookie') {
                anchorText = 'enforce token';
            } else if (selectedValue == 3) {
                anchorText = '3 legged';
            }

            form.prev('a')
                .html(anchorText)
                .attr('title', selectedValue)
                .css('display', 'inline');

            form.remove();
        }
    });
}
</script>

</div><!-- .entry -->

<div style="clear:both;"></div>
