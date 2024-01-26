<?php

require 'vendor/autoload.php';

dd(\OnixSystemsPHP\HyperfFeatureFlags\FeatureFlag\FeatureFlags::isEnabled('Support.slack-integration'));