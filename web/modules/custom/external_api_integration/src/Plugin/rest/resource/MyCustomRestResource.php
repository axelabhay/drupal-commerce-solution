<?php

namespace Drupal\external_api_integration\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a Custom REST Resource.
 *
 * @RestResource(
 *   id = "my_custom_rest_resource",
 *   label = @Translation("My Custom REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/external_api_integration/my_custom_rest_resource",
 *     "https://www.drupal.org/link-relations/create" = "/external_api_integration/my_custom_rest_resource"
 *   }
 * )
 */
class MyCustomRestResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing data.
   */
  public function get() {
    // Sample response for GET.
    $response = [
      'message' => 'This is a GET response from My Custom REST Resource.',
      'status' => 'success',
    ];

    return new ResourceResponse($response, 200);
  }

}
