<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace HDInnovations\CacheHook;

class CoffeeCache
{
    /**
     * Cache time in seconds
     *
     * seconds * seconds per minutes * hours per day * days
     * 60 * 60 * 24 * 1 = 1 day
     * 60 * 60 * 24 * 10 = 10 days
     *
     * @var int
     */
    public $cacheTime = 60 * 60 * 24 * 1;

    /**
     * @var string
     */
    private $cacheDirPath = '';

    /**
     * Cached filename
     * @var string
     */
    private $cachedFilename = '';

    /**
     * Enabled hosts list. Optional, leave it as empty array if you want to cache all domains.
     */
    public $enabledHosts = [];

    /**
     * List of enabled http status codes, default is 200 OK.
     * @var string[]
     */
    public $enabledHttpStatusCodes = [
        '200'
    ];

    /**
     * @var null|int
     */
    public $httpStatusCode = null;

    /**
     * @var string[]
     */
    public $excludeUrls = [];


    /**
     * CoffeeCache constructor.
     *
     */
    public function __construct()
    {
        //Init
        $this->cachedFilename = sha1($_SERVER['REQUEST_URI']);
        $this->cacheDirPath = storage_path().DIRECTORY_SEPARATOR.'coffeeCache';
    }

    /**
     * @return bool
     */
    public function isCacheAble ()
    {
        //init
        $domainShouldBeCached = false;

        if (sizeof($this->enabledHosts) > 0) {

            $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['SERVER_NAME'];

            foreach ($this->enabledHosts as $cachedHostName) {
                if (strpos($host, $cachedHostName) !== false) {
                    $domainShouldBeCached = true;
                    break;
                }
            }
        } else {
            $domainShouldBeCached = true;
        }

        return $_SERVER['REQUEST_METHOD'] === 'GET'
            && $domainShouldBeCached
            && !$this->detectExcludedUrl();
    }


    /**
     * Handle request for caching
     */
    public function handle ()
    {
        if ($this->isCacheAble()) {

            if (file_exists($this->cacheDirPath.$this->cachedFilename)
                && filemtime($this->cacheDirPath.$this->cachedFilename) + $this->cacheTime > time()) {
                echo file_get_contents($this->cacheDirPath.$this->cachedFilename);
                exit;
            } else {
                ob_start();
            }
        }
    }


    /**
     * Finalize cache. Write file to disk is caching is enabled
     */
    public function finalize ()
    {
        if ($this->isCacheAble() && $this->detectStatusCode()) {
            file_put_contents($this->cacheDirPath.$this->cachedFilename,  ob_get_contents());
            ob_end_clean();
            $this->handle();
        }
    }


    /**
     * @return bool
     */
    private function detectStatusCode ()
    {
        return in_array((string)$this->httpStatusCode, $this->enabledHttpStatusCodes);
    }


    /**
     * @return bool
     */
    private function detectExcludedUrl ()
    {
        if (sizeof($this->excludeUrls) > 0) {
            foreach ($this->excludeUrls as $excludeUrl) {
                if (strpos($_SERVER['REQUEST_URI'], $excludeUrl) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}