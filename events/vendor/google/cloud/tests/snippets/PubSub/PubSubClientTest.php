<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Tests\Snippets\PubSub;

use Google\Cloud\Dev\SetStubConnectionTrait;
use Google\Cloud\Dev\Snippet\SnippetTestCase;
use Google\Cloud\PubSub\Connection\ConnectionInterface;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use Prophecy\Argument;

/**
 * @group pubsub
 */
class PubSubClientTest extends SnippetTestCase
{
    const TOPIC = 'projects/my-awesome-project/topics/my-new-topic';
    const SUBSCRIPTION = 'projects/my-awesome-project/subscriptions/my-new-subscription';

    private $connection;
    private $client;

    public function setUp()
    {
        $this->connection = $this->prophesize(ConnectionInterface::class);
        $this->client = new \PubSubClientStub(['transport' => 'rest']);
    }

    public function testClassExample1()
    {
        $snippet = $this->snippetFromClass(PubSubClient::class, '__construct');
        $res = $snippet->invoke('pubsub');

        $this->assertInstanceOf(PubSubClient::class, $res->returnVal());
    }

    public function testCreateTopic()
    {
        $this->connection->createTopic(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'name' => self::TOPIC
            ]);

        $this->client->setConnection($this->connection->reveal());

        $snippet = $this->snippetFromMethod(PubSubClient::class, 'createTopic');
        $snippet->addLocal('pubsub', $this->client);

        $res = $snippet->invoke('topic');

        $this->assertInstanceOf(Topic::class, $res->returnVal());
        $this->assertEquals(self::TOPIC, $res->returnVal()->name());
        $this->assertEquals(self::TOPIC, $res->output());
    }

    public function testTopic()
    {
        $snippet = $this->snippetFromMethod(PubSubClient::class, 'topic');
        $snippet->addLocal('pubsub', $this->client);

        $this->connection->getTopic(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'name' => self::TOPIC
            ]);

        $this->client->setConnection($this->connection->reveal());

        $res = $snippet->invoke('topic');

        $this->assertInstanceOf(Topic::class, $res->returnVal());
        $this->assertEquals(self::TOPIC, $res->returnVal()->name());
        $this->assertEquals(self::TOPIC, $res->output());
    }

    public function testTopics()
    {
        $snippet = $this->snippetFromMethod(PubSubClient::class, 'topics');
        $snippet->addLocal('pubsub', $this->client);

        $this->connection->listTopics(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'topics' => [
                    ['name' => self::TOPIC]
                ]
            ]);

        $this->client->setConnection($this->connection->reveal());

        $res = $snippet->invoke('topics');

        $this->assertInstanceOf(\Generator::class, $res->returnVal());
        $this->assertEquals(self::TOPIC, $res->output());
    }

    public function testSubscribe()
    {
        $snippet = $this->snippetFromMethod(PubSubClient::class, 'subscribe');
        $snippet->addLocal('pubsub', $this->client);

        $this->connection->createSubscription(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'name' => self::SUBSCRIPTION,
                'topic' => self::TOPIC
            ]);

        $this->client->setConnection($this->connection->reveal());

        $res = $snippet->invoke('subscription');

        $this->assertInstanceOf(Subscription::class, $res->returnVal());
        $this->assertEquals(self::SUBSCRIPTION, $res->returnVal()->name());
        $this->assertEquals(self::SUBSCRIPTION, $res->returnVal()->info()['name']);
        $this->assertEquals(self::TOPIC, $res->returnVal()->info()['topic']);
    }

    public function testSubscription()
    {
        $snippet = $this->snippetFromMethod(PubSubClient::class, 'subscription');
        $snippet->addLocal('pubsub', $this->client);

        $this->connection->getSubscription(['subscription' => self::SUBSCRIPTION])
            ->shouldBeCalled()
            ->willReturn([
                'name' => self::SUBSCRIPTION,
                'topic' => self::TOPIC
            ]);

        $this->client->setConnection($this->connection->reveal());

        $res = $snippet->invoke('subscription');

        $this->assertInstanceOf(Subscription::class, $res->returnVal());
        $this->assertEquals(self::SUBSCRIPTION, $res->returnVal()->name());
        $this->assertEquals(self::SUBSCRIPTION, $res->returnVal()->info()['name']);
        $this->assertEquals(self::TOPIC, $res->returnVal()->info()['topic']);
    }

    public function testSubscriptions()
    {
        $snippet = $this->snippetFromMethod(PubSubClient::class, 'subscriptions');
        $snippet->addLocal('pubsub', $this->client);

        $this->connection->listSubscriptions(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'subscriptions' => [
                    ['name' => self::SUBSCRIPTION, 'topic' => self::TOPIC]
                ]
            ]);

        $this->client->setConnection($this->connection->reveal());

        $res = $snippet->invoke('subscriptions');
        $this->assertInstanceOf(\Generator::class, $res->returnVal());
        $this->assertEquals(self::SUBSCRIPTION, $res->output());
    }

    public function testConsume()
    {
        $message = [
            "message" => [
                "attributes" => [],
                "data" => base64_encode('content'),
                "message_id" => "message-id",
                "publish_time" => (new \DateTime)->format('c'),
            ],
            "subscription" => self::SUBSCRIPTION
        ];

        $snippet = $this->snippetFromMethod(PubSubClient::class, 'consume');
        $snippet->addLocal('pubsub', $this->client);
        $snippet->setLine(0, '$httpPostRequestBody = \''. json_encode($message) .'\';');

        $res = $snippet->invoke('message');
        $this->assertInstanceOf(Message::class, $res->returnVal());
    }
}
