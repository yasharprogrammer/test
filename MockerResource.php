<?php

namespace Tests\Unit\Mockers;

use App\Exceptions\WorkflowException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;

final class MockerResource
{
    private const MOCK_FILE_EXTENSION = '.json';

    private static string $mockerClass;

    private static string $mockDataPath;

    public static function setMockDataPath(string $mockDataPath): void
    {
        self::$mockDataPath = resource_path($mockDataPath);
    }

    public static function getMockDataPath(): string
    {
        return self::$mockDataPath;
    }

    public static function setMockerClass(string $mockerClass): void
    {
        self::$mockerClass = $mockerClass;
    }

    public static function getMockerClass(): string
    {
        return self::$mockerClass;
    }

    /**
     * @throws WorkflowException
     */
    public static function getCurrentMockerPath(): string
    {
        $mockerFilePath = self::getNormalizedPath();
        if (!is_file($mockerFilePath)) {
            throw new WorkflowException("Mock data file - $mockerFilePath not founded!");
        }

        return $mockerFilePath;
    }

    /**
     * @throws WorkflowException
     */
    public static function readMockData(?string $mockerClass = null): string
    {
        if (null !== $mockerClass) {
            self::setMockerClass($mockerClass);
        }

        return File::get(self::getCurrentMockerPath());
    }

    /**
     * @return string[]|array[]
     * @throws WorkflowException
     * @throws JsonException
     */
    public static function getMockData(?string $mockerClass = null): array
    {
        if (null !== $mockerClass) {
            self::setMockerClass($mockerClass);
        }

        $mockData = self::readMockData();

        if ('' === $mockData) {
            throw new WorkflowException('No data find for mock!');
        }

        return json_decode(
            $mockData,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private static function getNormalizedPath(): string
    {
        return rtrim(self::getMockDataPath(), '/')
            . '/'
            . Str::of(self::getMockerClass())->classBasename()->camel()
            . self::MOCK_FILE_EXTENSION;
    }
}
