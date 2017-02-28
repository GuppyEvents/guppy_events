# Google Cloud PHP Client
> Idiomatic PHP client for [Google Cloud Platform](https://cloud.google.com/) services.

[![Travis Build Status](https://travis-ci.org/GoogleCloudPlatform/google-cloud-php.svg?branch=master)](https://travis-ci.org/GoogleCloudPlatform/google-cloud-php/) [![codecov](https://codecov.io/gh/googlecloudplatform/google-cloud-php/branch/master/graph/badge.svg)](https://codecov.io/gh/googlecloudplatform/google-cloud-php)

* [Homepage](http://googlecloudplatform.github.io/google-cloud-php)
* [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs)

This client supports the following Google Cloud Platform services at a [Beta](#versioning) quality level:

* [Google BigQuery](#google-bigquery-beta) (Beta)
* [Google Stackdriver Logging](#google-stackdriver-logging-beta) (Beta)
* [Google Cloud Datastore](#google-cloud-datastore-beta) (Beta)
* [Google Cloud Storage](#google-cloud-storage-beta) (Beta)

This client supports the following Google Cloud Platform services at an [Alpha](#versioning) quality level:
* [Google Cloud Natural Language](#google-cloud-natural-language-alpha) (Alpha)
* [Google Cloud Pub/Sub](#google-cloud-pubsub-alpha) (Alpha)
* [Google Cloud Speech](#google-cloud-speech-alpha) (Alpha)
* [Google Cloud Translation](#google-cloud-translation-alpha) (Alpha)
* [Google Cloud Vision](#google-cloud-vision-alpha) (Alpha)

If you need support for other Google APIs, please check out the [Google APIs Client Library for PHP](https://github.com/google/google-api-php-client).

## Quick Start

```sh
$ composer require google/cloud
```

## Google BigQuery (Beta)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/bigquery/bigqueryclient)
- [Official Documentation](https://cloud.google.com/bigquery/docs)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\BigQuery\BigQueryClient;

$bigQuery = new BigQueryClient([
	'projectId' => 'my_project'
]);

// Get an instance of a previously created table.
$dataset = $bigQuery->dataset('my_dataset');
$table = $dataset->table('my_table');

// Begin a job to import data from a CSV file into the table.
$job = $table->load(
	fopen('/data/my_data.csv', 'r')
);

// Run a query and inspect the results.
$queryResults = $bigQuery->runQuery('SELECT * FROM [my_project:my_dataset.my_table]');

foreach ($queryResults->rows() as $row) {
    print_r($row);
}
```

## Google Stackdriver Logging (Beta)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/logging/loggingclient)
- [Official Documentation](https://cloud.google.com/logging/docs)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\Logging\LoggingClient;

$logging = new LoggingClient([
	'projectId' => 'my_project'
]);

// Get a logger instance.
$logger = $logging->logger('my_log');

// Write a log entry.
$logger->write('my message');

// List log entries from a specific log.
$entries = $logging->entries([
	'filter' => 'logName = projects/my_project/logs/my_log'
]);

foreach ($entries as $entry) {
    echo $entry->info()['textPayload'] . "\n";
}
```

## Google Cloud Datastore (Beta)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/datastore/datastoreclient)
- [Official Documentation](https://cloud.google.com/datastore/docs/)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\Datastore\DatastoreClient;

$datastore = new DatastoreClient([
    'projectId' => 'my_project'
]);

// Create an entity
$bob = $datastore->entity('Person');
$bob['firstName'] = 'Bob';
$bob['email'] = 'bob@example.com';
$datastore->insert($bob);

// Update the entity
$bob['email'] = 'bobV2@example.com';
$datastore->update($bob);

// If you know the ID of the entity, you can look it up
$key = $datastore->key('Person', '12345328897844');
$entity = $datastore->lookup($key);
```

## Google Cloud Storage (Beta)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/storage/storageclient)
- [Official Documentation](https://cloud.google.com/storage/docs)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

$storage = new StorageClient([
    'projectId' => 'my_project'
]);

$bucket = $storage->bucket('my_bucket');

// Upload a file to the bucket.
$bucket->upload(
    fopen('/data/file.txt', 'r')
);

// Download and store an object from the bucket locally.
$object = $bucket->object('file_backup.txt');
$object->downloadToFile('/data/file_backup.txt');
```

#### Stream Wrapper

```php
require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

$storage = new StorageClient([
    'projectId' => 'my_project'
]);
$storage->registerStreamWrapper();

$contents = file_get_contents('gs://my_bucket/file_backup.txt');
```

## Google Cloud Translation (Alpha)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/translate/translateclient)
- [Official Documentation](https://cloud.google.com/translation/docs)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\Translate\TranslateClient;

$translate = new TranslateClient([
    'key' => 'your_key'
]);

// Translate text from english to french.
$result = $translate->translate('Hello world!', [
    'target' => 'fr'
]);

echo $result['text'] . "\n";

// Detect the language of a string.
$result = $translate->detectLanguage('Greetings from Michigan!');

echo $result['languageCode'] . "\n";

// Get the languages supported for translation specifically for your target language.
$languages = $translate->localizedLanguages([
    'target' => 'en'
]);

foreach ($languages as $language) {
    echo $language['name'] . "\n";
    echo $language['code'] . "\n";
}

// Get all languages supported for translation.
$languages = $translate->languages();

foreach ($languages as $language) {
    echo $language . "\n";
}
```

## Google Cloud Natural Language (Alpha)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/naturallanguage/naturallanguageclient)
- [Official Documentation](https://cloud.google.com/natural-language/docs)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\NaturalLanguage\NaturalLanguageClient;

$language = new NaturalLanguageClient([
    'projectId' => 'my_project'
]);

// Analyze a sentence.
$annotation = $language->annotateText('Greetings from Michigan!');

// Check the sentiment.
if ($annotation->sentiment() > 0) {
    echo "This is a positive message.\n";
}

// Detect entities.
$entities = $annotation->entitiesByType('LOCATION');

foreach ($entities as $entity) {
    echo $entity['name'] . "\n";
}

// Parse the syntax.
$tokens = $annotation->tokensByTag('NOUN');

foreach ($tokens as $token) {
    echo $token['text']['content'] . "\n";
}
```

## Google Cloud Pub/Sub (Alpha)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/pubsub/pubsubclient)
- [Official Documentation](https://cloud.google.com/pubsub/docs)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\PubSub\PubSubClient;

$pubSub = new PubSubClient([
    'projectId' => 'my_project'
]);

// Get an instance of a previously created topic.
$topic = $pubSub->topic('my_topic');

// Publish a message to the topic.
$topic->publish([
	'data' => 'My new message.',
	'attributes' => [
		'location' => 'Detroit'
	]
]);

// Get an instance of a previously created subscription.
$subscription = $pubSub->subscription('my_subscription');

// Pull all available messages.
$messages = $subscription->pull();

foreach ($messages as $message) {
    echo $message->data() . "\n";
    echo $message->attribute('location');
}
```

## Google Cloud Speech (Alpha)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/speech/speechclient)
- [Official Documentation](https://cloud.google.com/speech/docs)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\Speech\SpeechClient;

$speech = new SpeechClient([
    'projectId' => 'my_project'
]);

// Recognize the speech in an audio file.
$results = $speech->recognize(
    fopen('/data/audio_sample.flac', 'r')
);

foreach ($results as $result) {
    echo $result['transcript'] . "\n";
    echo $result['confidence'] . "\n";
}
```

## Google Cloud Vision (Alpha)

- [API Documentation](http://googlecloudplatform.github.io/google-cloud-php/#/docs/latest/vision/visionclient)
- [Official Documentation](https://cloud.google.com/vision/docs)

#### Preview

```php
require 'vendor/autoload.php';

use Google\Cloud\Vision\VisionClient;

$vision = new VisionClient([
    'projectId' => 'my_project'
]);

// Annotate an image, detecting faces.
$image = $vision->image(
    fopen('/data/family_photo.jpg', 'r'),
    ['faces']
);

$annotation = $vision->annotate($image);

// Determine if the detected faces have headwear.
foreach ($annotation->faces() as $key => $face) {
    if ($face->hasHeadwear()) {
        echo "Face $key has headwear.\n";
    }
}
```

## Caching Access Tokens

By default the library will use a simple in-memory caching implementation, however it is possible to override this behavior by passing a [PSR-6](http://www.php-fig.org/psr/psr-6/) caching implementation in to the desired client.

The following example takes advantage of [Symfony's Cache Component](https://github.com/symfony/cache).

```php
require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

// Please take the proper precautions when storing your access tokens in a cache no matter the implementation.
$cache = new ArrayAdapter();

$storage = new StorageClient([
    'authCache' => $cache
]);
```

## Versioning

This library follows [Semantic Versioning](http://semver.org/).

Please note it is currently under active development. Any release versioned 0.x.y is subject to backwards incompatible changes at any time.

**Beta**: Libraries defined at a Beta quality level are expected to be mostly stable and we're working towards their release candidate. We will address issues and requests with a higher priority.

**Alpha**: Libraries defined at an Alpha quality level are still a work-in-progress and are more likely to get backwards-incompatible updates.

## Contributing

Contributions to this library are always welcome and highly encouraged.

See [CONTRIBUTING](CONTRIBUTING.md) for more information on how to get started.

## License

Apache 2.0 - See [LICENSE](LICENSE) for more information.
