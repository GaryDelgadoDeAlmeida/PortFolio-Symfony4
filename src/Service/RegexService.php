<?php

namespace App\Service;

interface RegexService {

    public const SECURE_PASSWORD = '/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/';
    public const ONLY_NUMERIC = '/[0-9]/i';
}