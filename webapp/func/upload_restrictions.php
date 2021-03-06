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
// Allowed file types
$upload_restrictions_ext_array = array("add_avatar" => array(".png",".gif",".jpg",".jpeg"),
                                        "add_logo" => array(".png",".gif",".jpg",".jpeg"),
                                        "add_attachment" => array(".zip", ".rar", ".7z", ".tar", ".gz", ".bz2"),
                                        "add_topo" => array(".png",".gif",".jpg",".jpeg",".tiff",".bmp",".pdf", ".svg", ".csv", ".gpx", ".kml", ".lox", ".mp4", ".zip", ".ai", ".tro", ".doc", ".docx", ".odt", ".trk", ".dpt", ".dxf", ".trk"));
// Max size in octets
$upload_restrictions_size_array = array("add_avatar" => 30000,
                                        "add_logo" => 30000,
                                        "add_attachment" => 500000,
                                        "add_topo" => 100000000);
?>
