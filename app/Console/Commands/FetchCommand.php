<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use msng\ImageFetcher\Fetcher;
use msng\ImageFetcher\Image;
use UnexpectedValueException;

class FetchCommand extends Command
{
    const FILE_PATH_ROOT = 'images';
    /**
     * @var string
     */
    protected $signature = 'fetch {url}';
    /**
     * @var string
     */
    protected $description = '指定された URL のページにある OG 画像を取得して保存します。';
    private $extensions = [
        'image/png' => 'png',
        'image/jpeg' => 'jpeg',
        'image/gif' => 'gif'
    ];
    /**
     * @var Fetcher
     */
    private $fetcher;

    /**
     * @param Fetcher $fetcher
     */
    public function __construct(Fetcher $fetcher)
    {
        parent::__construct();
        $this->fetcher = $fetcher;
    }

    public function handle()
    {
        // コマンド引数の URL を取得
        $url = $this->argument('url');


        // URL のウェブページにある OG 画像を取得する。
        // 返り値は msng\ImageFetcher\Image のインスタンス。
        $image = $this->fetcher->fetchFromWebPage($url);

        if (is_null($image)) {
            // 画像がない / 取れなかったときは null が返る
            $this->warn('画像が取得できませんでした。');
            exit;
        }

        // ログ表示
        $this->info('Image URL: ' . $image->getUrl());
        $this->info('Content Type: ' . $image->getContentType());

        $filename = $this->buildFilename($image);
        $filePath = $this->buildFilePath($filename);

        Storage::put($filePath, $image->getContent());
        $url = Storage::url($filePath);

        // ログ表示
        $this->info('Saved file to: ' . $filePath);
        $this->info('File URL: ' . $url);
    }

    private function buildFilename(Image $image): string
    {
        // 同じ内容のファイルは同じファイル名になるように content をハッシュ化してファイル名とする
        $filenameBody = sha1($image->getContent());

        $extension = $this->getExtensionForContentType($image->getContentType());

        return $filenameBody . '.' . $extension;
    }

    private function getExtensionForContentType(string $contentType): string
    {
        if (!array_key_exists($contentType, $this->extensions)) {
            throw new UnexpectedValueException('Unknown content type.');
        }

        return $this->extensions[$contentType];
    }

    /**
     * @param string $filename
     * @return string
     */
    private function buildFilePath(string $filename): string
    {
        /**
         * 同じディレクトリに大量のファイルが並ぶのを避けるため
         * ハッシュ化されたファイル名の頭2文字ずつを取ってディレクトリの階層にする
         */
        $paths = [
            self::FILE_PATH_ROOT,
            substr($filename, 0, 2),
            substr($filename, 2, 2),
            $filename
        ];

        return implode('/', $paths);
    }
}
