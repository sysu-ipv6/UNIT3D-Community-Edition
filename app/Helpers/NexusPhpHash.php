<?php

namespace App\Helpers\NexusPhpHash;

use Illuminate\Hashing\AbstractHasher;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use RuntimeException;

class NexusPhpHasher extends AbstractHasher implements HasherContract {

    public function __construct(array $options = []) {

    }

    protected function makeRandomSecret($secretLength = 20) {
        $secret = "";
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= chr(mt_rand(100, 120));
        }
        return $secret;
    }

    public function make($value, array $options = []) {
        $secret = $options['secret'] ?? $this->makeRandomSecret();
        $hash = md5($secret.$value.$secret);
        if ($hash === false) {
            throw new RuntimeException('md5 hashing not supported.');
        }
        return '$nexus$' . $secret . '$' . $hash;
    }

    public function check($value, $hashedValue, array $options = []) {
        list(, $identifier, $secret, ) = explode('$', $hashedValue);
        if ($identifier !== 'nexus') {
            throw new RuntimeException('not nexusphp password hash');
        }
        return $this->make($value, ['secret' => $secret]) === $hashedValue;
    }

    public function needsRehash($hashedValue, array $options = array()): bool {
        return false;
    }

}