<?php
declare(strict_types=1);

namespace Sparkframe\Tools;

// All the properties that can appear in a route url
define('INT_ROUTE_PROPERTY', '{:int}');
define('STR_ROUTE_PROPERTY', '{:str}');
define('WILDCARD_ROUTE_PROPERTY', '*');

// Shorthand route properties
define('INT_RP', INT_ROUTE_PROPERTY);
define('STR_RP', STR_ROUTE_PROPERTY);
define('WILD_RP', WILDCARD_ROUTE_PROPERTY);
