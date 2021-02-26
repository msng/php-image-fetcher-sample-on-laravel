# PHP image fetcher sample on Laravel

## 最初にやること

```
composer install
cp .env.example .env
php artisan key:generate
```

## 画像を取ってきて保存する

```
php artisan fetch {ウェブサイトの URL}
```

画像が取れたら `storage/app/images/` 以下に保存されます。

## Google Cloud Storage に保持する設定

`.env` の以下の箇所を書き換えます。

```
FILESYSTEM_DRIVER=gcs_public
GOOGLE_CLOUD_PROJECT_ID={GCP のプロジェクト ID}
GOOGLE_CLOUD_KEY_FILE={サービスアカウントのキーファイルへのパス}
GOOGLE_CLOUD_STORAGE_BUCKET={GCS のバケット名}
```

### 設定の記述があるところ

`config/filesystems.php` の `gcs_public` のところ
