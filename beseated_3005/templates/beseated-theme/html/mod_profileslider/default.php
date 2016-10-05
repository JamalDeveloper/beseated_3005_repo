<?php

defined('_JEXEC') or die;

?>

<div class="images-box bordered-box">
  <div class="row">
    <?php foreach ($images as $image): ?>
      <div class="col-md-4">
        <div class="item-box <?php echo $image->is_default ? 'default' : ''; ?>" data-image-id="<?php echo $image->image_id; ?>" data-element-id="<?php echo $image->element_id ?>">
          <div class="image" style="background-image: url( <?php echo 'images/beseated/'.$image->thumb_image; ?> );"></div>
          <button class="button delete" type="button">Delete</button>
          <button class="button set-as-default" type="button">Set as default</button>
        </div>        
      </div>
    <?php endforeach ?>
  </div>
  <label for="image" class="button button-upload">
    <input type="file" name="image" id="image-upload" />
    Upload image
  </label>
  <div class="upload-progress">
    <p>Upload progress</p>    
  </div>
</div>

<script>
  var spinnerTemplate = '<div class="spinner-wrapper"><div class="spinner"></div></div>';

  function deleteImage(event) {
    var element = $(event.currentTarget).parent();
    var params = { option: 'com_beseated', task: 'profile.deleteProfileImage', element_id: element.data('elementId'), image_id: element.data('imageId') };

    element.append($(spinnerTemplate));
    
    $.getJSON('index.php', params).always(function(response) {
      element.find('.spinner-wrapper').remove();

      if(response.success == false)
        return;

      $('[data-image-id=' + response.data.image_id + ']').parent().remove();     
    });
  }

  function setAsDefault(event) {
    var element = $(event.currentTarget).parent();
    var params = { option: 'com_beseated', task: 'profile.changeDefaultImage', element_id: element.data('elementId'), image_id: element.data('imageId') }; 

    element.append($(spinnerTemplate));

    $.getJSON('index.php', params).always(function(response) {
      element.find('.spinner-wrapper').remove();

      if(!response.success)
        return;

      $('.images-box .item-box').removeClass('default');
      $('[data-image-id=' + response.data.image_id + ']').addClass('default');      
    });
  }

  function uploadSend(event, data) {
    data.progressBar = $('<div class="bar"><div class="progress" style="width: 0%"></div></div>');
    $('.upload-progress').show().append(data.progressBar);
  }

  function uploadProgress(event, data) {
    var progress = parseInt(data.loaded / data.total * 100, 10);
    data.progressBar.find('.progress').animate({
      'width': progress + '%'
    })
  }

  function uploadDone(event, data) {
    var image = data.result.data;
    var template = '<div class="col-md-4">' +
      '<div class="item-box" data-image-id="' + image.image_id + '" data-element-id="' + image.element_id + '">' +
          '<div class="image" style="background-image: url( images/beseated/' + image.thumb_image + ' );"></div>' +
          '<button class="button delete" type="button">Delete</button>' +
          '<button class="button set-as-default" type="button">Set as default</button>' +
        '</div>'
      '</div>';

    var element = $(template);

    if(image.is_default) {
      element.addClass('default')
    }

    $('.images-box .row').prepend(element);

    data.progressBar.remove();

    if($('.upload-progress .bar').length === 0) {
      $('.upload-progress').hide();
    }
  }

  $('.images-box').on('click', '.delete', deleteImage);

  $('.images-box').on('click', '.set-as-default', setAsDefault);
  
  $('#image-upload').fileupload({
      url: 'index.php?option=com_beseated&task=profile.uploadImage',
      dataType: 'json',
      send: uploadSend,
      progress: uploadProgress,
      done: uploadDone
  });  
</script>