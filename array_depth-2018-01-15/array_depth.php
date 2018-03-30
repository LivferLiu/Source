<?php
				/* IMPORTANT: READ BEFORE DOWNLOADING, COPYING, INSTALLING OR USING.
						
				By downloading, copying, installing or using the software you agree to this license.
				If you do not agree to this license, do not download, install,
				copy or use the software.
				
				
										  License Agreement
									  For Array Depth class package   V 1.0.0
				
				Copyright (C) 2018, Akpe Aurelle Emmanuel Moïse Zinsou, all rights reserved.
				
				Redistribution and use in source and binary forms, with or without modification,
				are permitted provided that the following conditions are met:
				
				  * Redistribution's of source code must retain the above copyright notice,
					this list of conditions and the following disclaimer.
				
				  * Redistribution's in binary form must reproduce the above copyright notice,
					this list of conditions and the following disclaimer in the documentation
					and/or other materials provided with the distribution.
				
				  * The name of the copyright holders may not be used to endorse or promote products
					derived from this software without specific prior written permission.
				
				This software is provided by the copyright holders and contributors "as is" and
				any express or implied warranties, including, but not limited to, the implied
				warranties of merchantability and fitness for a particular purpose are disclaimed.
				In no event shall Akpe Aurelle Emmanuel Moïse Zinsou or contributors be liable for 
				any direct, indirect, incidental, special, exemplary, or consequential damages(including, 
				but not limited to, procurement of substitute goods or services;loss of use,  data, or 
				interruption) however caused and on any theory of profits; or business liability, 
				whether  in contract, strict liability, or tort (including negligence or otherwise)
				arising in any way out of the use of this software, even if advised of the possibility
				of such damage.

				EZAMA contact:leizmo@gmail.com*/
						
						
class ArrayD{
				/*return array depth (level of interleaving) */
				public static function epth($array,$reset=true){
					if(!function_exists('array_in')){
						function  array_in($array){
							if(is_array($array)){
								foreach($array as $val){
									if(is_array($val)) return true;
									
								}
								
							}
							return false;
						}
					}
					static $i=1;
					if($reset) {
						$i=1;
					}
					if(is_array($array)){
						if(array_in($array)){
							$i++;
							foreach($array as $v){
								if(array_in($v)) {
									self::epth($v,false);
								}
							}
						}
						
					}else{
						return 0;
					}
					
					return $i;
				}
				
				/* return all data types used in the given array */
				public static function typesof($array,$reset=true){
					static $types=[];
					if($reset) $types=[];
					if(is_array($array)){
						foreach($array as $v){
							if(is_array($v)){
								$types[]=gettype($v);
								self::typesof($v,false);
							}
							else{
								$types[]=gettype($v);
							}
						}
						return $types;
					}else{
						return [];
					}
				
				}
				
				/* return the data type(s) used in the first element of the array*/
				public static function signature($array){
					if(is_array($array)){
						reset($array);
						$current=current($array);
						if(is_array($current)&&!empty($current)){ 
						$types=typesof(current($array));
						array_unshift($types,'array');
						return $types;
						}
						else return array(gettype($current));
					}else{
						return array(gettype($array));
					}
				}
				
				/* return true if all the elements of the array have the same signature
					as the first element and the data type used in the array  */
				public static function homogeneous($array,&$type=null){
					if(is_array($array)){
						$types=self::typesof($array);
						$signature=self::signature($array);
						$chunks=array_chunk($types,count($signature));
						foreach($chunks as $vals){
							if($vals!==$signature){
								if($type) $type=null;
								return false;
							}
						}
						if($type){
							$values=array_count_values($types);
							unset($values['array']);
							$type=key($values);
						}
						return true;
					}else{
						if($type) $type=gettype($array);
						return false;
					}
				}
				/* return true if key signature of the first element of the array is the 
				same for all the elements in the array*/
				
				public static function rowsandcol($array,&$ksignature){
					if(is_array($array)){
						if(is_array($vc=current($array)))
						$ksignature=array_keys($array[key($vc)]);
						else return false;
						foreach($array as $k=>$v){
							if(!is_array($v)) return false;	
							else{
								if(array_keys($v)!==$ksignature) return false;
							}				
						}
						return	true;
					}
					return false;
				}
}


	/*return array depth (level of interleaving) */

	function array_depth($array){
		return arrayD::epth($array);	
	}
	/* return all data types used in the given array */
	function typesof($array){
		return arrayD::typesof($array);		
	}
	/* return true if all the elements of the array have the same signature
		as the first element and the data type used in the array  */
	function homogeneous($array,&$type=null){
		return arrayD::homogeneous($array,$type);		
	}
	/* return the type(s) used in the first element of the array*/
	function signature($array){
		return arrayD::signature($array);		
	}
	/* return true if key signature of the first element of the array is the 
		same for all the elements in the array */
	function rowsandcol($array,&$ksignature=null){
		return arrayD::rowsandcol($array,$ksignature);		
	}

?>