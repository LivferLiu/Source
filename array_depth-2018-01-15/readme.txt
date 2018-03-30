This  class can help achieving this goal:

	- return the given  array depth (level of interleaving) 
	- return all data types used in a given array 
	- return  whether or not the array is homogeneous 
	- return the signature of a given array 
	- return whether or not an array is suitable for rows and columns selection's process.
	
	
	for example when you need to do some recursive foreach on an array without knowing the array ,it
	may be useful to know the real depth programmatically.
	
	it may be useful to know all data types used in an array or even just in the first element of an array
	as you may see in the homogeneous method implementation.
	
	
	An array must be homogeneous in many applications: for example : to compute a chi square value for a data's cross table, 
	all values must be integer(may be double number but without floating point/comma) so if there is  any double value in this array 
	the result will be corrupted.
	
	when you will use array_column on an array for some operations ,it is impossible to be sure that all cells exists
	so you got some incomplete results for example 
	
	$array=array(array(1,2,3),array(0,2,3),array(4,5,6),array(2,2));
	
	print_r(array_column($array,2));
	
	//return 
	
		Array
	(
		[0] => 3
		[1] => 3
		[2] => 6
	)
	
	As you may see the last array has just been ignored .Sometimes it is cool but sometimes you don't want that
	because in a loop you may obtain ugly warning just like offset {number} doesn't exists...It is in this
	situation that you may need to know whether or not an array is suitable for rows and columns selection's process
	and the real keys signature.
	
	
	for a how to use example refer to the file test.php 
	report but via the forum or contact me at leizmo@gmail.com.