<?php

namespace App\Service;

interface RegexService {

    public const ONLY_NUMERIC = '/[0-9]/i';
    public const SECURE_PASSWORD = '/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/';
}