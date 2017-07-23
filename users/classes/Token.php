<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class Token {
	public static function generate(){
        $tokenName = Config::get('session/token_name');
	    //changed by will on 7/23/2017 to add more consistant tokens across multiple tabls
        // only generate if the last timestamp to generate for this session is over 30 minutes
        if (Session::exists('csrf_last_generate_time')) {
            $timestamp = Session::get('csrf_last_generate_time');
        } else {
            $timestamp = time();
            Session::put('csrf_last_generate_time', $timestamp);
        }
        $seconds_difference = time() - $timestamp;
        if ($seconds_difference > 60* 30) {
            return Session::put($tokenName, md5(uniqid()));
        } else {
            if (Session::exists($tokenName)) {
                return Session::get($tokenName);
            } else {
                return Session::put($tokenName, md5(uniqid()));
            }

        }

	}

	public static function check($token){
		$tokenName = Config::get('session/token_name');

		if (Session::exists($tokenName) && $token === Session::get($tokenName)) {
		    // Changed by Will : cannot delete token after every check as we are reusing same token while work will be done
		//	Session::delete($tokenName);
			return true;
		}
		return false;
	}
}
