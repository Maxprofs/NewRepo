
<?php
include("config.php");
global $conn,$stmt;
global $category_array;
$category_array=array();
global $product_array,$limits,$start,$numbr,$total_records,$page_id,$total_pages,$numbr1,$numbr3,$lmt1,$lmt,$starts,$offset1,$offset,$limit,$image_women,$image_men,$image_kids,$image_digital,$image_sport;
global $c_name,$c_parent_id;
//$c_name=" ";
//$c_parent_id=0;
$product_array=array();
$image_women=array();
$image_men=array();
$image_kids=array();
$image_digital=array();
$image_sport=array();
$total_records=0;
$limits=6; 
function addProduct()
{
		global $conn,$stmt;

		$id12=$_POST['p_id'];
		$name12=$_POST['p_name'];
		$price12=$_POST['p_price'];
		$category12=$_POST['p_category'];
		
		if(isset($_FILES['p_image']))
		{

			$temp=$_FILES['p_image']['tmp_name'];
			$nm=$_FILES['p_image']['name'];
			if(move_uploaded_file($temp,"../uploadsnew/".$nm))
			{
				$image12=$nm;
				//echo "image uploaded sucessfully";
				
			} else
			{
				echo "no image inserted";
			}
		}
		$stmt=$conn->prepare("INSERT INTO product_table (id,name,price,image,category) VALUES(?,?,?,?,?)");
		$stmt->bind_param("ssiss",$id12,$name12,$price12,$image12,$category12);
		$execute=$stmt->execute();
		if($execute==false)
			{
				$_SESSION['error']="gfg";
				echo "no data inserted";
			}
			else
			{
				$_SESSION['message']="gfgh";
				//echo "data inserted successfully";
			}
			$stmt->close();
			$conn->close();
			
	}

function getAllProducts()
{	
	global $product_array,$stmt,$conn,$image_women,$image_men,$image_kids,$image_digital,$image_sport;
	$stmt=$conn->prepare("SELECT * FROM product_table");
$stmt->execute();
$res=$stmt->bind_result($idp,$namep,$pricep,$imagep,$categoryp);
if($res==false)
{
  echo "no result binded";
}
while($stmt->fetch())
{
  array_push($product_array, array("id"=>$idp,"name"=>$namep,"price"=>$pricep,"image"=>$imagep,"category"=>$categoryp));
}

getImagebyCategory($product_array,$image_women,$image_men,$image_kids,$image_digital,$image_sport);
}

function getImagebyCategory($product_array,$image_women,$image_men,$image_kids,$image_digital,$image_sport)
{
    global $product_array,$image_women,$image_men,$image_kids,$image_digital,$image_sport;
  foreach ($product_array as $key => $value) 
  {
    # code...

    if($product_array[$key]['category']=="Men")
        {
          array_push($image_men,array("id"=>$product_array[$key]['id'],"name"=>$product_array[$key]['name'],"price"=>$product_array[$key]['price'],"image"=>$product_array[$key]['image'],"category"=>$product_array[$key]['category']));
        }
        else if($product_array[$key]['category']=="Women")
        {
        array_push($image_women,array("id"=>$product_array[$key]['id'],"name"=>$product_array[$key]['name'],"price"=>$product_array[$key]['price'],"image"=>$product_array[$key]['image'],"category"=>$product_array[$key]['category']));
        }
        else if($product_array[$key]['category']=="Kids")
        {
         array_push($image_kids,array("id"=>$product_array[$key]['id'],"name"=>$product_array[$key]['name'],"price"=>$product_array[$key]['price'],"image"=>$product_array[$key]['image'],"category"=>$product_array[$key]['category']));
        }
         else if($product_array[$key]['category']=="Digital")
        {
          array_push($image_digital,array("id"=>$product_array[$key]['id'],"name"=>$product_array[$key]['name'],"price"=>$product_array[$key]['price'],"image"=>$product_array[$key]['image'],"category"=>$product_array[$key]['category']));
        }
          else if($product_array[$key]['category']=="Sports")
        {
          array_push($image_sport,array("id"=>$product_array[$key]['id'],"name"=>$product_array[$key]['name'],"price"=>$product_array[$key]['price'],"image"=>$product_array[$key]['image'],"category"=>$product_array[$key]['category']));
        }
  }
  //print_r($image_sport);

	
}

function checkPageId()
{
	if(!isset($_GET["page_id"]))
	{
	  $page_id=0;
	}
	else
	{
	  $page_id=$_GET["page_id"];
	}	
	return $page_id;
}

//$start=$page_id*$limits;
   function getCategory()
	{
	global $category_array;
	global $conn,$stmt;
	 $category_array=array();
	 $stmt=$conn->prepare("SELECT * FROM category_table");
	 $stmt->execute();
	 $stmt->bind_result($id13,$name13,$parent_id13);
	 while ($stmt->fetch()) 
	 {
	   array_push($category_array, array("id"=>$id13,"name"=>$name13,"parent_id"=>$parent_id13));
	 }
	 return $category_array;
	}

	function setCategory()
	{
		global $c_name,$c_parent_id;
		$c_name=$_POST['category_name'];
		if($_POST['dd_category']==" ")
		{
			echo "";
			$c_parent_id=0;
			put_asParentCategory($c_name,$c_parent_id);
			
		}
		else
		{
			
			$category_selected=$_POST['dd_category'];
			makeSubCategory($category_selected,$c_name);
		}
	}
		function put_asParentCategory($c_name,$c_parent_id)
		{
			global $conn,$stmt,$c_name,$c_parent_id;

			$stmt=$conn->prepare("INSERT INTO category_table (category_name,parent_id) VALUES(?,?)");
			$bnd=$stmt->bind_param("si",$c_name,$c_parent_id)	;
			$stmt->execute();
			if(false==$bnd)
			{
				echo "not inserted";
			}
			else
			{
				echo "entered as parent category";
			}
			$stmt->close();
			$conn->close();
		}

		function makeSubCategory($category_selected,$c_name1)
		{
			global $conn,$stmt;
			$dd_selected=$category_selected;
			$new_category=$c_name1;
			//echo $new_category;
			$stmt=$conn->prepare("SELECT * FROM category_table WHERE category_name=?");
			if($stmt==false)
			{
				echo "error";
			}
			else
			{
				echo "entered as child category";
			}
			$stmt->bind_param("s",$dd_selected);
			$stmt->execute();
			$stmt->bind_result($sub_cat_id,$ab,$cd);
			while($stmt->fetch())
			{
				$cat_parent_id=$sub_cat_id;
			}
			$stmt->close();
			$stmt=$conn->prepare("INSERT INTO category_table (category_name,parent_id) VALUES(?,?)");
			$stmt->bind_param("si",$new_category,$cat_parent_id);
			$stmt->execute();
			$stmt->close();
			$conn->close();
		}
	function getNumberOfProducts()
	{
		global $conn,$stmt,$start,$limits,$numbr,$total_records,$page_id,$total_pages,$numbr1,$numbr3;
		$page_id=checkPageId();
		$query="SELECT COUNT(*) FROM product_table "; 
		if(isset($_GET['ctgry']))
		{
			$cat_to_show=$_GET['ctgry'];
			$query.="WHERE category=?";
			$stmt=$conn->prepare($query);
			$stmt->bind_param("s",$cat_to_show);
			//echo 12345;
			
		}
		else if(!empty($_POST['check_list']))
			  {
			  	$query.="WHERE category=?";
			  	foreach ($_POST['check_list'] as $value)
     			{
     				$stmt=$conn->prepare($query);
					$stmt->bind_param("s",$value);
					$stmt->execute();
					$stmt->bind_result($numbr1); 
					while($stmt->fetch()) 
					{
     			 	 $total_records= $total_records+$numbr1;
     			  	
     			  # code...
     			}
     			}
     			//echo $total_records;
     			$total_pages=ceil($total_records/6);
     			$start=$page_id*$limits;
     			return $start;
			  }
			  else if(isset($_GET['checkbox2']))
					{
						$query.="WHERE category=?";
						foreach ($_GET['checkbox2'] as $value2)
						 {
						 				  		//echo $value2[2];
						 	     				$stmt=$conn->prepare($query);
						 						$stmt->bind_param("s",$value2);
						 						$stmt->execute();
						 						$stmt->bind_result($numbr3); 
						 						while($stmt->fetch()) 
						 						{
						 	     			 	 $total_records= $total_records+$numbr3;
						 	     			  	
						 	     			  # code...

						 	     			}
						 	     			
						 	     			}
						 	     			//echo $total_records;
						 	     			$total_pages=ceil($total_records/6);
						 	     			$start=$page_id*$limits;
						 	     			return $start;
						
						}
						//return 0;
					
		else
		{
			$stmt=$conn->prepare($query);
		}
		
		$stmt->execute();
		$stmt->bind_result($numbr);
		
		
		while($stmt->fetch()) 
		{
		  $total_records= $numbr;
		  //echo $total_records;
		  # code...
		}
		$total_pages=ceil($total_records/6);
		$start=$page_id*$limits;
		//echo $start;
		return $start;
	}
	 function getCategoryFilter($start)
	 {
	 	  global $conn,$stmt,$limits,$start,$product_array,$lmt1,$lmt,$starts,$offset1,$offset,$limit;
	 	  //$cttoselect=$_GET['ctgry'];
	 	  $offset=$start;
	 	  $offset1=$start;
	 	  $lmt1=$limits;
	 	  $starts=$start;
	 	  $lmt=$limits;
	 	  $limit=$limits;
	 	  $query="SELECT * FROM product_table  WHERE category=? LIMIT ?,?";
	 	  $stmt=$conn->prepare($query);
	 	  if(isset($_GET['ctgry']))
	 	  {
	 	  	$cat_to_select=$_GET['ctgry']; 
	 	 	 $stmt->bind_param("sii",$cat_to_select,$start,$limits);
	 	  }
	 	  else if(!empty($_POST['check_list']))
	 	  {
	 	  	foreach ($_POST['check_list'] as $value1)
	 	  	 {
	 	  		
	 	  		$stmt->bind_param("sii",$value1,$offset,$lmt);
	 	  		$stmt->execute();
	 	  		$res=$stmt->bind_result($idpr1,$namepr1,$pricepr1,$imagepr1,$categorypr1);
		 	  	while($stmt->fetch())
		 	  	{
		 	  	  array_push($product_array, array("id"=>$idpr1,"name"=>$namepr1,"price"=>$pricepr1,"image"=>$imagepr1,"category"=>$categorypr1));
		 	  	}
	 	 	 }
	 	 	 return $product_array;

	 	  }
	 	  else if(isset($_GET['checkbox2']))
					{
						
						foreach ($_GET['checkbox2'] as $value4)
						 {
						 	 	  		$stmt->bind_param("sii",$value4,$offset1,$lmt1);
						 	 	  		$stmt->execute();
						 	 	  		$res=$stmt->bind_result($idpr11,$namepr11,$pricepr11,$imagepr11,$categorypr11);
						 		 	  	while($stmt->fetch())
						 		 	  	{
						 		 	  	  array_push($product_array, array("id"=>$idpr11,"name"=>$namepr11,"price"=>$pricepr11,"image"=>$imagepr11,"category"=>$categorypr11));
						 		 	  	}
						 	 	 	 }
						 	 	 	 return $product_array;
						 }
						
	 	  else
	 	  {
	 	  	 $query="SELECT * FROM product_table LIMIT ?,?";
		 	 $stmt=$conn->prepare($query);
		 	 $stmt->bind_param("ii",$starts,$limit);
	 	  }
	 	
	 	  $stmt->execute();
	 	  $res=$stmt->bind_result($idpr,$namepr,$pricepr,$imagepr,$categorypr);
	 	if($res==false)
	 	{
	 	  echo "no result binded";
	 	}
	 	while($stmt->fetch())
	 	{
	 	  array_push($product_array, array("id"=>$idpr,"name"=>$namepr,"price"=>$pricepr,"image"=>$imagepr,"category"=>$categorypr));
	 	}
	 	return $product_array;
	 }

 
 ?>