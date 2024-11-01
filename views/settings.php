<div class="wrap">
  <h1><?php esc_html_e( get_admin_page_title() ); ?></h1>

  <form action="#" method="post">
    <h3><?php esc_html_e( 'Add new word to link', plugin_slug ); ?></h3>
    <table class="form-table" style="width: auto !important; margin-bottom: 3em;">
      <tbody>
        <tr>
          <td>
            <input type="text" name="word_to_link_word" id="word_to_link_word" placeholder="<?php esc_html_e( 'Word', plugin_slug ); ?>">
          </td>          
          <td>
            <input type="text" name="word_to_link_link" id="word_to_link_link" placeholder="<?php esc_html_e( 'Link', plugin_slug ); ?>">
          </td>
          <td>
            <?php esc_html_e( 'Number of occurrences', plugin_slug ); ?>
            <select name="word_to_link_num" id="word_to_link_num">
              <option value="-1"><?php esc_html_e( 'All', plugin_slug );?></option>
              <?php for( $i = 0; $i <= 10; $i++ ) : ?>
                <option value="<?php esc_html_e($i)?>"><?php esc_html_E($i)?></option>
              <?php endfor; ?>
            </select>
          </td>
          <td>
            <?php esc_html_e( 'Case Sensitive', plugin_slug ); ?>
            <select name="word_to_link_casesensitive" id="word_to_link_casesensitive">
              <option value="no" selected="selected"><?php esc_html_e( 'no', plugin_slug );?></option>
              <option value="si"><?php esc_html_e( 'yes', plugin_slug );?></option>
            </select>
          </td>    
          <td>
            <?php esc_html_e( 'Full Word', plugin_slug ); ?>
            <select name="word_to_link_fullword" id="word_to_link_fullword">
              <option value="no" selected="selected"><?php esc_html_e( 'no', plugin_slug );?></option>
              <option value="si"><?php esc_html_e( 'yes', plugin_slug );?></option>
            </select>
          </td>    
          <td>
            <?php esc_html_e( 'Target _blank', plugin_slug ); ?>
            <select name="word_to_link_targetblank" id="word_to_link_targetblank">
              <option value="no" selected="selected"><?php echo esc_html_e( 'no', plugin_slug );?></option>
              <option value="si"><?php echo esc_html_e( 'yes', plugin_slug );?></option>
            </select>
          </td>
          <td>
            <?php esc_html_e( 'Enable Stats', plugin_slug ); ?>
            <select name="word_to_link_enable_stats" id="word_to_link_enable_stats">
              <option value="no" selected="selected"><?php esc_html_e( 'no', plugin_slug );?></option>
              <option value="si"><?php esc_html_e( 'yes', plugin_slug );?></option>
            </select>
          </td>                          
          <td>
            <input type="submit" value="<?php esc_attr_e( 'Save word to link', plugin_slug ); ?>" class="button-primary">
          </td>
        </tr>
      </tbody>
    </table>
  </form>

  <table class="widefat">
    <thead>
      <tr>
        <th><?php esc_html_e( 'Word', plugin_slug ); ?></th>
        <th class="column-primary"><?php esc_html_e( 'Link', plugin_slug ); ?></th>
        <th><?php esc_html_e( 'Number of occurrences', plugin_slug ); ?></th>
        <th><?php esc_html_e( 'Case Sensitive', plugin_slug ); ?></th>
        <th><?php esc_html_e( 'Full Word', plugin_slug ); ?></th>
        <th><?php esc_html_e( 'Target _blank', plugin_slug ); ?></th>
        <th><?php esc_html_e( 'Enable Stats', plugin_slug ); ?></th>        
        <th><?php esc_html_e( 'Actions', plugin_slug ); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php
        if ( ! empty( $words_to_links ) ) {
          foreach ( $words_to_links as $pos => $word_to_link ) {
      ?>
      <tr>
        <td><?php esc_html_e( $word_to_link['keyword'] ); ?></td>
        <td><a href="<?php esc_html_e( $word_to_link['link'] ); ?>" target="_blank"><?php esc_html_e( $word_to_link['link'] ); ?></a></td>
        <td><?php ( $word_to_link['num'] == -1 ) ? esc_html_e( 'All', plugin_slug ) : esc_html_e( $word_to_link['num'] ); ?></td>
        <td><?php esc_html_e( $word_to_link['caseSensitive'] ); ?></td>
        <td><?php esc_html_e( $word_to_link['fullWord'] ); ?></td>
        <td><?php @esc_html_e( $word_to_link['targetBlank'] ); ?></td>
        <td>
          <?php @esc_html_e( $word_to_link['enableStats'] ); 
            if( isset($word_to_link['enableStats']) AND $word_to_link['enableStats'] == 'si' ) : ?>
            [<a href="<?php echo get_site_url().'/'.$this->customSlug.'/'.sanitize_title($word_to_link['keyword']) ?>" target="_blank">link</a>]
          <?php
            endif; 
          ?>
        </td>
        <td>
          <a href="#" class="edit-simple_linkify" 
            data-word="<?php esc_attr_e( $word_to_link['keyword'] ); ?>" 
            data-link="<?php esc_attr_e( $word_to_link['link'] ); ?>" 
            data-num="<?php esc_attr_e( $word_to_link['num'] ); ?>"
            data-casesensitive="<?php esc_attr_e( $word_to_link['caseSensitive'] ); ?>"
            data-fullword="<?php esc_attr_e( $word_to_link['fullWord'] ); ?>"
            data-targetblank="<?php esc_attr_e( $word_to_link['targetBlank'] ); ?>"
            data-enable_stats="<?php esc_attr_e( $word_to_link['enableStats'] ); ?>">
            <?php esc_html_e( 'Edit', plugin_slug ); ?></a> | 
          <a href="#" class="delete-simple_linkify" data-word="<?php esc_attr_e( $word_to_link['keyword'] ); ?>" data-pos="<?php echo $pos?>">
            <?php esc_html_e( 'Delete', plugin_slug ); ?></a>
        </td>
      </tr>
      <?php
          }
        } else {
      ?>
      <tr>
        <td colspan="3"><?php esc_html_e( 'No words to links found.', plugin_slug ); ?></td>
      </tr>
      <?php
        }
      ?>
    </tbody>
  </table>

</div>

<script>
jQuery(document).ready(function($) {
  $('.edit-simple_linkify').on('click', function(e) {
    e.preventDefault();

    $('#word_to_link_word').val($(this).data('word'));
    $('#word_to_link_link').val($(this).data('link'));
    $('#word_to_link_num').val($(this).data('num'));
    $('#word_to_link_casesensitive').val($(this).data('casesensitive'));
    $('#word_to_link_fullword').val($(this).data('fullword'));
    $('#word_to_link_targetblank').val($(this).data('targetblank'));
    $('#word_to_link_enable_stats').val($(this).data('enable_stats'));
  });

  $('.delete-simple_linkify').on('click', function(e) {
    e.preventDefault();

    if ( confirm( '<?php esc_html_e( 'Are you sure you want to delete this word to link?', plugin_slug ); ?>' ) ) {
      location.href = 'admin.php?page=simple-keyword-to-link-delete-element&pos='+$(this).data('pos');
    }
  });
});
</script>
           
