<?php

namespace Hedron\Drupal8\Parser;

use Hedron\Command\CommandStackInterface;
use Hedron\GitPostReceiveHandler;
use Hedron\Parser\BaseParser;

/**
 * @Hedron\Annotation\Parser(
 *   pluginId = "drupal_8_settings"
 * )
 */
class Drupal8Settings extends BaseParser {

  /**
   * {@inheritdoc}
   */
  public function destroy(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $settings_path = "{$this->getDataDirectoryPath()}/sites/default";
    if ($this->getFileSystem()->exists($settings_path . DIRECTORY_SEPARATOR . "settings.php")) {
      unlink($settings_path . DIRECTORY_SEPARATOR . "settings.php");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function parse(GitPostReceiveHandler $handler, CommandStackInterface $commandStack) {
    $settings_path = "{$this->getDataDirectoryPath()}/sites/default";
    if (!file_exists("$settings_path/settings.php") && file_exists("$settings_path/default.settings.php")) {
      // @todo add a foreach loop around something like:
      // $environment->getSites() so that we can write the default to each site
      // in a multisite install.
      copy("$settings_path/default.settings.php", "$settings_path/settings.php");
      chmod("$settings_path/settings.php", 0775);

      // @todo resepct the previous today about getSites() and also allow a
      // yaml file in the repository to be used to configure $settings.
      $append = "if (isset(\$_SERVER['DELIVERY_SETTINGS_DIR']) && file_exists(\$_SERVER['DELIVERY_SETTINGS_DIR'] . \"/{$this->getClientDirectoryName()}.inc\")) {
  require \$_SERVER['DELIVERY_SETTINGS_DIR'] . \"/{$this->getClientDirectoryName()}.inc\";
}";

      file_put_contents("$settings_path/settings.php", PHP_EOL.$append.PHP_EOL, FILE_APPEND | LOCK_EX);
    }
  }

}
