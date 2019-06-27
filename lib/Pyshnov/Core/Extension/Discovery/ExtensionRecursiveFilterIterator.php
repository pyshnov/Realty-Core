<?php
/**
 * Данный класс взять из Drupal 8
 * @link https://github.com/drupal/drupal/blob/8.3.x/core/lib/Drupal/Core/Extension/Discovery/RecursiveExtensionFilterIterator.php
 */

namespace Pyshnov\Core\Extension\Discovery;

/**
 * Filters a RecursiveDirectoryIterator to discover extensions.
 *
 * To ensure the best possible performance for extension discovery, this
 * filter implementation hard-codes a range of assumptions about directories
 * in which Drupal extensions may appear and in which not. Every unnecessary
 * subdirectory tree recursion is avoided.
 *
 * The list of globally ignored directory names is defined in the
 * RecursiveExtensionFilterIterator::$blacklist property.
 *
 * In addition, all 'config' directories are skipped, unless the directory path
 * ends with 'modules/config', so as to still find the config module provided by
 * Drupal core and still allow that module to be overridden with a custom config
 * module.
 *
 * Lastly, ExtensionDiscovery instructs this filter to additionally skip all
 * 'tests' directories at regular runtime, since just with Drupal core only, the
 * discovery process yields 4x more extensions when tests are not ignored.
 *
 * @see ExtensionDiscovery::scan()
 * @see ExtensionDiscovery::scanDirectory()
 *
 * @todo Use RecursiveCallbackFilterIterator instead of the $acceptTests
 *   parameter forwarding once PHP 5.4 is available.
 */
class ExtensionRecursiveFilterIterator extends \RecursiveFilterIterator {

    /**
     * Список имен каталогов для сканирования.
     * Только эти имена каталогов учитываются при запуске рекурсии файловой системы в пути поиска.
     */
    protected $whitelist = [
        'modules',
        'templates',
    ];

    /**
     * Список имен каталогов, которые следует пропускать при рекурсии.
     */
    protected $blacklist = [
        'src',
        'lib',
        'vendor',
        'assets',
        'css',
        'files',
        'images',
        'js',
        'templates',
        'includes'
    ];

    public function __construct(\RecursiveIterator $iterator, array $blacklist = []) {
        parent::__construct($iterator);
        $this->blacklist = array_merge($this->blacklist, $blacklist);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren() {
        $filter = parent::getChildren();
        $filter->blacklist = $this->blacklist;

        return $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function accept() {
        $name = $this->current()->getFilename();
        // FilesystemIterator::SKIP_DOTS only skips '.' and '..', but not hidden
        // directories (like '.git').
        if ($name[0] == '.') {
            return false;
        }
        if ($this->isDir()) {
            // If this is a subdirectory of a base search path, only recurse into the
            // fixed list of expected extension type directory names. Required for
            // scanning the top-level/root directory; without this condition, we would
            // recurse into the whole filesystem tree that possibly contains other
            // files aside from Drupal.
            if ($this->current()->getSubPath() == '') {
                return in_array($name, $this->whitelist, TRUE);
            }

            // Accept the directory unless the name is blacklisted.
            return !in_array($name, $this->blacklist, true);
        }
        else {
            // Принимаем только файлы info.yml
            return $name == 'info.yml';
        }
    }

}
