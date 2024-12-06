/**
 * @file
 * Scripts for the color palette.
 */

(function ($, Drupal, drupalSettings) {

  jQuery.ajax({
      url: 'https://api.github.com/users/axelabhay/repos',
      method: 'GET',
      success: function (repos) {
          $.each(repos, function (index, item) {
              // Alert repository names.
              alert(index + 1 + '. ' + item.name);
          });
      }
  });

})(jQuery, Drupal, drupalSettings);