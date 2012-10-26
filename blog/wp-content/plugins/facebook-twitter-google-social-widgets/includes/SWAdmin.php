<?php
function sw_admin() {
  ?>  
  <div class="wrap">
  <?php
  echo "<h2>" . __( 'Social Widgets Options', SW_DEF_STRING) . "</h2>";

  if($_POST['sw_hidden'] == 'Y') {
    $facebook = $_POST['sw_facebook'];
    $twitter = $_POST['sw_twitter'];
    $google = $_POST['sw_google'];
    $displayonposts = $_POST['sw_displayonposts'];
    $displayonpages = $_POST['sw_displayonpages'];
    $displayonhomepage = $_POST['sw_displayonhomepage'];
    $displayoncategories = $_POST['sw_displayoncategories'];
    $displayontags = $_POST['sw_displayontags'];
    $displayontaxonomies = $_POST['sw_displayontaxonomies'];
    $displayondates = $_POST['sw_displayondates'];
    $displayonauthors = $_POST['sw_displayonauthors'];
    $displayonsearches = $_POST['sw_displayonsearches'];
    $displayonattachments = $_POST['sw_displayonattachments'];
    $displayabovepost = $_POST['sw_displayabovepost'];
    $displaybelowpost = $_POST['sw_displaybelowpost'];
    $cssstyleall = $_POST['sw_cssstyleall'];
    $cssstylewidget = $_POST['sw_cssstylewidget'];
    $contentfilterpriority = $_POST['sw_contentfilterpriority'];
    $supportplugin = $_POST['sw_supportplugin'];
    ?>  
    <div class="updated">
      <p>
    <?php  
    if (
      ($facebook != 0 && (($facebook == $twitter) || ($facebook == $google))) ||
      ($twitter != 0 && ($twitter == $google))
    ) {
      echo '<strong>' . __('Error while saving options.', SW_DEF_STRING) . '</strong><br />';
      _e('Please make sure that each position for a widget is only chosen once!', SW_DEF_STRING);
    } elseif ($facebook == 0 && $twitter == 0 && $google == 0) {
      echo '<strong>' . __('Error while saving options.', SW_DEF_STRING) . '</strong><br />';
      _e('At least one widget needs to be displayed!', SW_DEF_STRING);
    } elseif ($displayonposts != 1 && $displayonpages != 1 &&
              $displayonhomepage != 1 && $displayoncategories != 1 &&
              $displayontags != 1 && $displayontaxonomies != 1 &&
              $displayondates != 1 && $displayonauthors != 1 &&
              $displayonsearches != 1 && $displayonattachments != 1) {
      echo '<strong>' . __('Error while saving options.', SW_DEF_STRING) . '</strong><br />';
      _e('The widgets need to be displayed at least on one page!', SW_DEF_STRING);
    } elseif ($displayabovepost != 1 && $displaybelowpost != 1) {
      echo '<strong>' . __('Error while saving options.', SW_DEF_STRING) . '</strong><br />';
      _e('The widgets need to be displayed either above the posts, below them or both!', SW_DEF_STRING);
    } elseif (!is_numeric($contentfilterpriority)) {
      echo '<strong>' . __('Error while saving options.', SW_DEF_STRING) . '</strong><br />';
      _e('The content filter priority needs to be an integer value!', SW_DEF_STRING);
    } else {
      update_option('sw_facebook', $facebook);
      update_option('sw_twitter', $twitter);
      update_option('sw_google', $google);
      update_option('sw_displayonposts', $displayonposts);
      update_option('sw_displayonpages', $displayonpages);
      update_option('sw_displayonhomepage', $displayonhomepage);
      update_option('sw_displayoncategories', $displayoncategories);
      update_option('sw_displayontags', $displayontags);
      update_option('sw_displayontaxonomies', $displayontaxonomies);
      update_option('sw_displayondates', $displayondates);
      update_option('sw_displayonauthors', $displayonauthors);
      update_option('sw_displayonsearches', $displayonsearches);
      update_option('sw_displayonattachments', $displayonattachments);
      update_option('sw_displayabovepost', $displayabovepost);
      update_option('sw_displaybelowpost', $displaybelowpost);
      update_option('sw_cssstyleall', $cssstyleall);
      update_option('sw_cssstylewidget', $cssstylewidget);
      update_option('sw_contentfilterpriority', $contentfilterpriority);
      update_option('sw_supportplugin', $supportplugin);
      ?>  
      <strong><?php _e('Options saved.', SW_DEF_STRING); ?></strong>
      <?php
    }
    ?>  
      </p>
    </div>  
    <?php
  }

  ?>
  <p>
    <?php echo sprintf(__('If you like this plug-in, please <a href="%s">consider a donation</a> to keep it free. Thank you!', SW_DEF_STRING),
                        'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donation%40breitschopp.de&item_name=Donation%20for%20Social%20Widgets%20WordPress%20Plug-In&no_shipping=0&no_note=1&tax=0&currency_code=EUR&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8'); ?>
  </p>
  <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="hosted_button_id" value="C6ZWWJADZNCYU">
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
  </form>
  <?php

  $facebook = get_option('sw_facebook');
  $twitter = get_option('sw_twitter');
  $google = get_option('sw_google');
  $displayonposts = get_option('sw_displayonposts');
  $displayonpages = get_option('sw_displayonpages');
  $displayonhomepage = get_option('sw_displayonhomepage');
  $displayoncategories = get_option('sw_displayoncategories');
  $displayontags = get_option('sw_displayontags');
  $displayontaxonomies = get_option('sw_displayontaxonomies');
  $displayondates = get_option('sw_displayondates');
  $displayonauthors = get_option('sw_displayonauthors');
  $displayonsearches = get_option('sw_displayonsearches');
  $displayonattachments = get_option('sw_displayonattachments');
  $displayabovepost = get_option('sw_displayabovepost');
  $displaybelowpost = get_option('sw_displaybelowpost');
  $cssstyleall = get_option('sw_cssstyleall');
  $cssstylewidget = get_option('sw_cssstylewidget');
  $contentfilterpriority = get_option('sw_contentfilterpriority');
  $supportplugin = get_option('sw_supportplugin');
  if ($facebook == '') {
    $facebook = 1;
    $twitter = 2;
    $google = 3;
    $supportplugin = 1;
  }
  if ($displayonposts == '' && $displayonpages == '' &&
      $displayonhomepage == '' && $displayoncategories == '' &&
      $displayontags == '' && $displayontaxonomies == '' &&
      $displayondates == '' && $displayonauthors == '' &&
      $displayonsearches == '' && $displayonattachments == '') {
    $displayonposts = 1;
    $displayonpages = 1;
  }
  if ($displayabovepost == '' && $displaybelowpost == '')
    $displaybelowpost = 1;
  if ($contentfilterpriority == '') $contentfilterpriority = 10;
  ?>
    <form name="sw_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <input type="hidden" name="sw_hidden" value="Y">
      <table>
        <tr>
          <td>
            <?php    echo "<h4>" . __( 'Display Order of Social Widgets', SW_DEF_STRING) . "</h4>"; ?>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td><?php _e("Facebook:", SW_DEF_STRING); ?></td>
                <td>
                  <select name="sw_facebook">
                    <option value="0"<?php if ($facebook == 0) echo ' selected="selected"'; ?>><?php _e("Don't display", SW_DEF_STRING); ?></option>
                    <option value="1"<?php if ($facebook == 1) echo ' selected="selected"'; ?>><?php _e("Position 1", SW_DEF_STRING); ?></option>
                    <option value="2"<?php if ($facebook == 2) echo ' selected="selected"'; ?>><?php _e("Position 2", SW_DEF_STRING); ?></option>
                    <option value="3"<?php if ($facebook == 3) echo ' selected="selected"'; ?>><?php _e("Position 3", SW_DEF_STRING); ?></option>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php _e("Twitter:", SW_DEF_STRING); ?></td>
                <td>
                  <select name="sw_twitter">
                    <option value="0"<?php if ($twitter == 0) echo ' selected="selected"'; ?>><?php _e("Don't display", SW_DEF_STRING); ?></option>
                    <option value="1"<?php if ($twitter == 1) echo ' selected="selected"'; ?>><?php _e("Position 1", SW_DEF_STRING); ?></option>
                    <option value="2"<?php if ($twitter == 2) echo ' selected="selected"'; ?>><?php _e("Position 2", SW_DEF_STRING); ?></option>
                    <option value="3"<?php if ($twitter == 3) echo ' selected="selected"'; ?>><?php _e("Position 3", SW_DEF_STRING); ?></option>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php _e("Google+:", SW_DEF_STRING); ?></td>
                <td>
                  <select name="sw_google">
                    <option value="0"<?php if ($google == 0) echo ' selected="selected"'; ?>><?php _e("Don't display", SW_DEF_STRING); ?></option>
                    <option value="1"<?php if ($google == 1) echo ' selected="selected"'; ?>><?php _e("Position 1", SW_DEF_STRING); ?></option>
                    <option value="2"<?php if ($google == 2) echo ' selected="selected"'; ?>><?php _e("Position 2", SW_DEF_STRING); ?></option>
                    <option value="3"<?php if ($google == 3) echo ' selected="selected"'; ?>><?php _e("Position 3", SW_DEF_STRING); ?></option>
                  </select>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <hr />  
            <?php    echo "<h4>" . __( 'Display social widgets on these pages', SW_DEF_STRING) . "</h4>"; ?>  
          </td> 
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td>
                  <input type="checkbox" name="sw_displayonposts" value="1"<?php if ($displayonposts == 1) echo ' checked'; ?>> <?php _e("Posts", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayonpages" value="1"<?php if ($displayonpages == 1) echo ' checked'; ?>> <?php _e("Pages", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayonhomepage" value="1"<?php if ($displayonhomepage == 1) echo ' checked'; ?>> <?php _e("Homepage", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayoncategories" value="1"<?php if ($displayoncategories == 1) echo ' checked'; ?>> <?php _e("Categories", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayontags" value="1"<?php if ($displayontags == 1) echo ' checked'; ?>> <?php _e("Tags", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayontaxonomies" value="1"<?php if ($displayontaxonomies == 1) echo ' checked'; ?>> <?php _e("Taxonomies", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayondates" value="1"<?php if ($displayondates == 1) echo ' checked'; ?>> <?php _e("Dates", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayonauthors" value="1"<?php if ($displayonauthors == 1) echo ' checked'; ?>> <?php _e("Authors", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayonsearches" value="1"<?php if ($displayonsearches == 1) echo ' checked'; ?>> <?php _e("Searches", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displayonattachments" value="1"<?php if ($displayonattachments == 1) echo ' checked'; ?>> <?php _e("Attachments", SW_DEF_STRING); ?>
                  <br /><br />
                  <em><?php _e("Tip: Don't display the social widgets on pages with excerpts as this leads to broken code.", SW_DEF_STRING); ?></em>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <hr />  
            <?php    echo "<h4>" . __( 'Display position of social widgets', SW_DEF_STRING) . "</h4>"; ?>  
          </td> 
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td>
                  <input type="checkbox" name="sw_displayabovepost" value="1"<?php if ($displayabovepost == 1) echo ' checked'; ?>> <?php _e("Display social widgets above posts", SW_DEF_STRING); ?><br />
                  <input type="checkbox" name="sw_displaybelowpost" value="1"<?php if ($displaybelowpost == 1) echo ' checked'; ?>> <?php _e("Display social widgets below posts", SW_DEF_STRING); ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <hr />  
            <?php    echo "<h4>" . __( 'Other Settings', SW_DEF_STRING) . "</h4>"; ?>
          </td> 
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td><?php _e("Additional CSS styles applied to whole widget box:", SW_DEF_STRING); ?></td>
                <td><input type="text" name="sw_cssstyleall" value="<?php echo $cssstyleall; ?>" size="20"> <?php _e('(e. g. "margin-bottom:20px")', SW_DEF_STRING); ?></td>
              </tr>
              <tr>
                <td><?php _e("Additional CSS styles applied to every widget:", SW_DEF_STRING); ?></td>
                <td><input type="text" name="sw_cssstylewidget" value="<?php echo $cssstylewidget; ?>" size="20"> <?php _e('(e. g. "margin-right:10px")', SW_DEF_STRING); ?></td>
              </tr>
              <tr>
                <td><?php _e("Content filter priority:", SW_DEF_STRING); ?></td>
                <td><input type="text" name="sw_contentfilterpriority" value="<?php echo $contentfilterpriority; ?>" size="20"> <?php _e('(plug-in priority (default: 10))', SW_DEF_STRING); ?></td>
              </tr>
              <tr>
                <td colspan="2"><input type="checkbox" name="sw_supportplugin" value="1"<?php if ($supportplugin == 1) echo ' checked'; ?>> <?php _e("Support this free plug-in with a small powered by link at your page footer. Thank you!", SW_DEF_STRING); ?></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Save options', SW_DEF_STRING) ?>" />
      </p>
    </form>
  </div>
  <?php
}

function sw_admin_actions() {  
    add_options_page(
      'Social Widgets Options',
      'Social Widgets',
      'manage_options',
      'social-widgets-options',
      'sw_admin'
      );
}
add_action('admin_menu', 'sw_admin_actions');

function sw_plugin_action_links($links, $file) 
{
  if ($file == SW_PLUGIN_PATH) {
    $settingslink = '<a href="options-general.php?page=social-widgets-options">' . __('Settings', SW_DEF_STRING) . '</a>';
    array_unshift($links, $settingslink);
  }

  return $links;
}
add_filter('plugin_action_links', 'sw_plugin_action_links', 10, 2);
?>
