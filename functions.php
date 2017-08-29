
<?php
include("config.php");
global $conn,$stmt;
global $category_array;
$category_array=array();
global $product_array,$limits,$start,$numbr,$total_records,$page_id;
$product_array=array();
$limits=6;
if(!isset($_GET["page_id"]))
{
  $page_id=0;
}
else
{
  $page_id=$_GET["page_id"];
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
	}

	function getNumberOfProducts()
	{
		global $conn,$stmt,$start,$limits,$numbr,$total_records,$page_id;
		$query="SELECT COUNT(*) FROM product_table "; 
		if(isset($_GET['ctgry']))
		{
			$cat_to_show=$_GET['ctgry'];
			$query.="WHERE category=?";
			$stmt=$conn->prepare($query);
			$stmt->bind_param("s",$cat_to_show);
		}
		$stmt=$conn->prepare($query);
		
		$stmt->bind_result($numbr);
		$stmt->execute();
		
		while($stmt->fetch()) {
		  $total_records= $numbr;
		  # code...
		}
		$total_pages=ceil($total_records/6);
		$start=$page_id*$limits;
	}
	 function getCategoryFilter()
	 {
	 	  global $conn,$stmt,$limits,$start,$product_array;
	 	  $cttoselect=$_GET['ctgry'];
	 	   
	 	  echo $limits.$start;
	 	    $cat_to_select=$_GET['ctgry'];
	 	    $stmt=$conn->prepare("SELECT * FROM product_table  WHERE category=? LIMIT ?,?");

	 	  $stmt->bind_param("sii",$cat_to_select,$start,$limits);
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
	 }

 
 ?>