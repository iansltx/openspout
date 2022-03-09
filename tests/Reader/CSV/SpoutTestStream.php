<?php

namespace OpenSpout\Reader\CSV;

/**
 * Custom stream that reads CSV files located in the tests/resources/csv folder.
 * For example: spout://foobar will point to tests/resources/csv/foobar.csv.
 */
class SpoutTestStream
{
    public const CLASS_NAME = __CLASS__;

    public const PATH_TO_CSV_RESOURCES = 'tests/resources/csv/';
    public const CSV_EXTENSION = '.csv';

    /** @var int */
    private $position;

    /** @var resource */
    private $fileHandle;

    /**
     * @param string $path
     * @param int    $flag
     *
     * @return array
     */
    public function url_stat($path, $flag)
    {
        $filePath = $this->getFilePathFromStreamPath($path);

        return stat($filePath);
    }

    /**
     * @param string $path
     * @param string $mode
     * @param int    $options
     * @param string $opened_path
     *
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->position = 0;

        // the path is like "spout://csv_name" so the actual file name correspond the name of the host.
        $filePath = $this->getFilePathFromStreamPath($path);
        $this->fileHandle = fopen($filePath, $mode);

        return true;
    }

    /**
     * @param int $numBytes
     *
     * @return string
     */
    public function stream_read($numBytes)
    {
        $this->position += $numBytes;

        return fread($this->fileHandle, $numBytes);
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        return $this->position;
    }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return bool
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        $result = fseek($this->fileHandle, $offset, $whence);
        if (-1 === $result) {
            return false;
        }

        if (SEEK_SET === $whence) {
            $this->position = $offset;
        } elseif (SEEK_CUR === $whence) {
            $this->position += $offset;
        }
        // not implemented

        return true;
    }

    /**
     * @return bool
     */
    public function stream_close()
    {
        return fclose($this->fileHandle);
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        return feof($this->fileHandle);
    }

    /**
     * @param string $streamPath
     *
     * @return string
     */
    private function getFilePathFromStreamPath($streamPath)
    {
        $fileName = parse_url($streamPath, PHP_URL_HOST);

        return self::PATH_TO_CSV_RESOURCES.$fileName.self::CSV_EXTENSION;
    }
}