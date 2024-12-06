<?php

namespace Drupal\axl_rate;

use Drupal\comment\CommentInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Database\Connection;

/**
 * Rating service.
 */
class RatingService {


  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a RatingService instance.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * To manage product rating.
   *
   * @param \Drupal\comment\CommentInterface $comment
   *   Comment interface.
   */
  public function manageProductRate(CommentInterface $comment): void {
    $rate = $comment->field_rating->rating;
    $product_id = $comment->entity_id->target_id;
    $entity_type = $comment->entity_type->value;
    $comment_type = $comment->comment_type->target_id;

    if (empty($rate) || empty($product_id)) {
      return;
    }

    // Get current product ratings.
    $comments = $this->getProductRatings($product_id, $entity_type, $comment_type);
    $comments[] = $rate;

    $avg_rate = $this->roundToNearestRate(array_sum($comments) / count($comments));

    // Update product rating.
    $this->updateProductRating($product_id, $avg_rate);
  }

  /**
   * Update average rating of existing product.
   *
   * @param int $pid
   *   Product ID.
   * @param int $rate
   *   Average rate for the product.
   * @return void
   */
  public function updateProductRating($pid, $rate): void {
    // Update product rataing.
    $product = Product::load($pid);
    $product->set('field_product_average_rating', $rate);
    $product->save();
  }

  /**
   * Get all rating of specific product.
   *
   * @param int $product_id
   *   Products ID.
   */
  public function getProductRatings($product_id, $entity_type, $comment_type) : array {

    $query = $this->database->select('comment__field_rating', 'cfr');
    $query->join('comment_field_data', 'cfd', 'cfd.cid = cfr.entity_id');
    $query->fields('cfr', ['field_rating_rating', 'entity_id']);
    $query->condition('cfd.comment_type', $comment_type);
    $query->condition('cfd.entity_type', $entity_type);
    $query->condition('cfd.field_name', 'field_product_review');
    $query->condition('cfd.entity_id', $product_id);
    $result = $query->execute()->fetchAll();

    $arr_rate = [];
    foreach ($result as $row) {
      $arr_rate[$row->entity_id] = $row->field_rating_rating;
    }

    return $arr_rate;
  }

  /**
   * Rounds a value to the nearest 20, 40, 60, 80, or 100.
   *
   * @param float|int $value
   *   The input value to be rounded.
   */
  public function roundToNearestRate($value): int {
    // Array of allowed rounding values.
    $rounding_values = [20, 40, 60, 80, 100];

    // Find the closest value from the array.
    $closest = NULL;
    foreach ($rounding_values as $target) {
      if ($closest === NULL || abs($value - $target) < abs($value - $closest)) {
        $closest = $target;
      }
    }

    return $closest;
  }

}
