<?php
/**
 * This file is part of GrottoCenter.
 *
 * GrottoCenter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GrottoCenter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with GrottoCenter.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright (c) 2009-2012 Cl�ment Ronzon
 * @license http://www.gnu.org/licenses/agpl.txt
 */
$real_path = realpath(__FILE__);
$real_path = substr($real_path,0,strlen($real_path)-20);
ini_set("session.save_path",$real_path."/sessions");
session_start();
?>
