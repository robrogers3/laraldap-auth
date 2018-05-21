<?php

namespace robrogers3\laradauth\Hashing;

use phpseclib\Crypt\AES;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;


class AESHasher  implements HasherContract
{

    /**
     * Create a new hasher instance.
     *
     * @param  app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get information about the given hashed value.
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        return array(PASSWORD_DEFAULT,'AES', []);
    }


    /**
     * @param $value
     * @param array $options
     * @return string
     * @throws \Exception
     */
    public function make($value, array $options = [])
    {
        $aes = new AES;

        $key = config('hashing.aes.key');

        if (!$key) {
            throw new \InvalidArgumentException('AES key not found. Did you set one.');
        }

        try {
            $aes->setKey($key);

            $hash = $aes->encrypt($value);

            if ($hash === false) {
                throw new RuntimeException('AES hashing not supported.');
            }

            return base64_encode($hash);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $value
     * @param $hashedValue
     * @param array $options
     * @return bool
     * @throws \Exception
     */
    public function check($value, $hashedValue, array $options = [])
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        $aes = new AES;

        $key = config('hashing.aes.key');

        if (!$key) {
            throw new \InvalidArgumentException('AES key not found. Did you set one.');
        }

        try {
            $aes->setKey($key);

            $hash = base64_encode($aes->encrypt($value));

            if ($hash === false) {
                throw new RuntimeException('AES hashing not supported.');
            }


        } catch (\Exception $e) {
            throw $e;
        }

        return $hash == $hashedValue;
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }

}
