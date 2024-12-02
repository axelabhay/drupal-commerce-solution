<?php

namespace Drupal\external_api_integration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a form for external API integration.
 */
class GithubApiIntegrationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'external_api_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['github_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GitHub Username'),
      '#description' => $this->t('Enter your GitHub username to retrieve details.'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $username = $form_state->getValue('github_username');
    $api_url = 'https://api.github.com/users/' . $username;

    try {
      // Make a GET request to the GitHub API.
      $response = \Drupal::httpClient()->get($api_url, [
        'headers' => [
          'Accept' => 'application/vnd.github.v3+json',
        ],
      ]);

      // Decode the JSON response.
      $data = json_decode($response->getBody()->getContents(), TRUE);


      // Check if the response contains relevant data.
      if (isset($data['id'])) {
        $message = $this->t('
        GitHub profile fetched successfully!
        <div>
          <img src="@avatar_url" alt="Avatar of @username" style="width:100px;height:100px;border-radius:50%;">
          <p><strong>Username:</strong> @username</p>
          <p><strong>GitHub ID:</strong> @id</p>
        </div>', [
          '@id' => $data['id'],
          '@username' => $data['login'],
          '@avatar_url' => $data['avatar_url'],
        ]);
        
        // Add the message as a status.
        $this->messenger()->addStatus($this->t('<div>@message</div>', ['@message' => $message]));
      }
      else {
        $this->messenger()->addError($this->t('Could not retrieve details for the username: @username', [
          '@username' => $username,
        ]));
      }
    }
    catch (RequestException $e) {
      // Handle request errors (e.g., network issues or invalid username).
      $this->messenger()->addError($this->t('An error occurred while trying to fetch details: @message', [
        '@message' => $e->getMessage(),
      ]));
    }
  }

}
