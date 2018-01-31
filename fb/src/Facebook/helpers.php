<?php

function d($str)
{
	return trim(html_entity_decode($str, ENT_QUOTES, 'UTF-8'));
}
