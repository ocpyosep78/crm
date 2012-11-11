<?php

function canEditEvent($user, $creator, $target=NULL)
{
	# 1. Edit own events (from or to), always granted
	if( $user == $creator || $user == $target ) return true;

	# 2. Developer and admin permission always granted
	if( getSes('id_profile') <= 2 ) return true;

	# 3. Groups of users who can edit eachother's events
	$groups[] = array('mantunez', 'rdelossantos', 'gperdomo');
	foreach( $groups as $group ){
		if( in_array($user, $group) && in_array($creator, $group) ){
			return true;
		}
	}

	# 4. Pairs of users where first one can edit second one's events
	$allowed = array(
//			array('thisOneCanEdit', 'thisOnesEvents'),
	);
	if( in_array(array($user, $creator), $allowed) ) return true;

	# None of the above
	return false;
}

function getUserImg($userid)
{
	$path = "app/images/users/{$userid}.png";
	return is_file($path) ? $path : 'app/images/noavatar.png';
}