<?php

namespace Drupal\Tests\cp_users\ExistingSite;

use Drupal\Tests\vsite\ExistingSiteJavascript\VsiteExistingSiteJavascriptTestBase;

/**
 * Class CpUsersMainTests.
 *
 * @group functional-javascript
 * @group wip
 * @package Drupal\Tests\cp_users\ExistingSite
 */
class CpUsersMainTest extends VsiteExistingSiteJavascriptTestBase {

  /**
   * The group tests are being run in.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup([
      'type' => 'personal',
      'uid' => 1,
      'path' => [
        'alias' => '/site01',
      ],
    ]);
  }

  /**
   * Tests for adding and removing users.
   */
  public function testAddExistingUser() {
    try {
      $account = $this->entityTypeManager->getStorage('user')->load(1);
      $account->passRaw = 'admin';
      $this->drupalLogin($account);
      $username = $this->randomString();
      $user = $this->createUser([], $username, FALSE);
      $this->visit('/site01/cp/users');
      $page = $this->getCurrentPage();
      $page->clickLink('+ Add a member');
      $this->assertSession()->waitForElement('css', '#drupal-modal--content');
      $page->clickLink('Add an Existing User');
      $page->fillField('member-entity', substr($username, 0, 3));
      $this->assertSession()->waitOnAutocomplete();
      $element = $page->find('css', '#ui-id-2');
      $this->assertNotNull($element, 'cannot find ui-id-2');
      $element->click();

      $page->selectFieldOption('role', 'personal-member');
      $page->pressButton("Save");
      $this->assertSession()->assertWaitOnAjaxRequest();
      $this->assertTrue($page->hasContent($username), "Username $username not found on page.");
    }
    catch (\Exception $e) {
      \file_put_contents(REQUEST_TIME . '.jpg', $this->getSession()->getScreenshot());
      $page = $this->getCurrentPage();
      \file_put_contents(REQUEST_TIME . '.txt', $page->getContent());
      $this->fail(\get_class($e) . ' in test: ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine());
    }
  }

}
