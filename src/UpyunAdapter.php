<?php
/**
 * User: ylk
 * Date: 2017/3/15
 * Time: 13:46
 */
namespace Axdlee\UpyunStorage;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Upyun\Upyun;

class UpyunAdapter extends AbstractAdapter
{
    /**
     * @var
     */
    protected $config;
    /**
     * @var
     */
    protected $client;
    public function __construct($config)
    {
        $this->config = $config;
        $this->client = new Upyun($config);
    }
    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        if (gettype($contents) == 'resource') {
            $contents = stream_get_contents($contents);
        }
        $object = $this->applyPathPrefix($path);
        try {
            $result = $this->client->write($object, $contents);
        } catch (\Exception $e) {
            return false;
        }
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->write($path, $resource, $config);
    }
    /**
     * @param $path
     * @param $filePath
     * @param Config $config
     *
     * @return array|bool
     */
    public function writeFile($path, $filePath, Config $config)
    {
        $object = $this->applyPathPrefix($path);
        try {
            $result = $this->client->write($object, file_get_contents($filePath));
        } catch (\Exception $e) {
            return false;
        }
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        return $this->write($path, $contents, $config);
    }
    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->write($path, $resource, $config);
    }
    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        try {
            $this->copy($path, $newpath);
            $this->delete($path);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $object = $this->applyPathPrefix($path);
        $newObject = $this->applyPathPrefix($newpath);
        try {
            $contents = $this->client->read($object);
            $this->client->write($newObject, $contents);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $object = $this->applyPathPrefix($path);
        return $this->client->delete($object);
    }
    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        $objectDir = $this->applyPathPrefix($dirname);
        return $this->client->deleteDir($objectDir);
    }
    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        $objectDir = $this->applyPathPrefix($dirname);
        try {
            $result = $this->client->createDir($objectDir);
        } catch (\Exception $e) {
            return false;
        }
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $object = $this->applyPathPrefix($path);
        try {
            $result = $this->client->has($object);
        } catch (\Exception $e) {
            return false;
        }
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $object = $this->applyPathPrefix($path);
        try {
            $result['contents'] = $this->client->read($object);
        } catch (\Exception $e) {
            return false;
        }
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        try {
            if (!($result = $this->read($path))) {
                return false;
            }
            $result['stream'] = fopen('php://memory', 'r+');
            fwrite($result['stream'], $result['contents']);
            rewind($result['stream']);
            unset($result['contents']);
        } catch (\Exception $e) {
            return false;
        }
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $object = $this->applyPathPrefix($path);
        $result = $this->client->info($object);
        return $this->formatUpyunMetaData($result);
    }
    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }
    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return false;
    }
    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }
    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        return true;
    }
    /**
     * @param $metadata
     *
     * @return bool | array
     */
    protected function formatUpyunMetaData($metadata)
    {
        $originParam = ['x-upyun-file-size', 'x-upyun-file-date'];
        if (gettype($metadata) != 'array') {
            return false;
        }
        foreach ($originParam as $param) {
            if (!array_key_exists($param, $metadata)) {
                return false;
            }
        }
        $newMetaData = $metadata;
        foreach ($originParam as $param) {
            switch ($param) {
                case 'x-upyun-file-size':
                    $newMetaData['size'] = $newMetaData[$param];
                    unset($newMetaData[$param]);
                    break;
                case 'x-upyun-file-date':
                    $newMetaData['timestamp'] = $newMetaData[$param];
                    unset($newMetaData[$param]);
                    break;
                default:
                    break;
            }
        }
        return $newMetaData;
    }
}