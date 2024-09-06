<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Engine\Contract;

if (PHP_VERSION_ID > 80000 && SWOOLE_VERSION_ID >= 50000) {
    interface ChannelInterface
    {
        /**
         * @param float|int $timeout [optional] = -1
         */
        public function push(mixed $data, float $timeout = -1): bool;

        /**
         * @param float $timeout seconds [optional] = -1
         * @return mixed when pop failed, return false
         */
        public function pop(float $timeout = -1): mixed;

        /**
         * Swow: When the channel is closed, all the data in it will be destroyed.
         * Swoole: When the channel is closed, the data in it can still be popped out, but push behavior will no longer succeed.
         */
        public function close(): bool;

        public function getCapacity(): int;

        public function getLength(): int;

        public function isAvailable(): bool;

        public function hasProducers(): bool;

        public function hasConsumers(): bool;

        public function isEmpty(): bool;

        public function isFull(): bool;

        public function isReadable(): bool;

        public function isWritable(): bool;

        public function isClosing(): bool;

        public function isTimeout(): bool;
    }
} else {
    interface ChannelInterface
    {
        /**
         * @param mixed $data [required]
         * @param float|int $timeout [optional] = -1
         * @return bool
         */
        public function push($data, $timeout = -1);

        /**
         * @param float $timeout seconds [optional] = -1
         * @return mixed when pop failed, return false
         */
        public function pop($timeout = -1);

        /**
         * Swow: When the channel is closed, all the data in it will be destroyed.
         * Swoole: When the channel is closed, the data in it can still be popped out, but push behavior will no longer succeed.
         * @return mixed
         */
        public function close(): bool;

        /**
         * @return int
         */
        public function getCapacity();

        /**
         * @return int
         */
        public function getLength();

        /**
         * @return bool
         */
        public function isAvailable();

        /**
         * @return bool
         */
        public function hasProducers();

        /**
         * @return bool
         */
        public function hasConsumers();

        /**
         * @return bool
         */
        public function isEmpty();

        /**
         * @return bool
         */
        public function isFull();

        /**
         * @return bool
         */
        public function isReadable();

        /**
         * @return bool
         */
        public function isWritable();

        /**
         * @return bool
         */
        public function isClosing();

        /**
         * @return bool
         */
        public function isTimeout();
    }
}
