<grid columns=12>
  <c span=row>
    <label class="_stool-label" for="<?php echo $id; ?>" <?php echo $tooltip . $condition; ?>>
    <span class="_stool-label-title"><?php echo $label; ?></span>
    <?php
      $settings = array( 'textarea_rows' => 15, 'editor_class'  => '_stool-input', );
      wp_editor($value, $id, $settings);
    ?>
    </label>
  </c>
</grid>