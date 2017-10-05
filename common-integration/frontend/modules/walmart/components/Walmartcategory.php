<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 3/7/17
 * Time: 11:24 AM
 */
namespace frontend\modules\walmart\components;

use yii\base\Component;

class Walmartcategory extends Component
{
    public static function getrootcategory()
    {
        $rootcategory = ['Animal', 'ArtAndCraftCategory' , 'Baby' , 'CarriersAndAccessoriesCategory', 'ClothingCategory', 'Electronics',
            'FurnitureCategory',  'FoodAndBeverageCategory',  'FootwearCategory', 'GardenAndPatioCategory', 'HealthAndBeauty', 'Home', 'JewelryCategory',
            'Media','MusicalInstrument', 'OfficeCategory', 'OtherCategory', 'OccasionAndSeasonal','Photography',
            'SportAndRecreation','ToolsAndHardware', 'ToysCategory', 'Vehicle', 'WatchesCategory'
             ];

        return $rootcategory;
    }

    public static function getchildcategory($rootcategory = null)
    {
        $childcategory = [];

        switch ($rootcategory) {
            case 'HealthAndBeauty' : {
                $childcategory = [
                    'MedicalAids', 'Optical', 'MedicineAndSupplements', 'HealthAndBeautyElectronics', 'PersonalCare'
                ];
                break;
            }
            case 'Home' : {
                $childcategory = [
                    'Bedding', 'LargeAppliances', 'HomeOther'
                ];
                break;
            }
            case 'FurnitureCategory' : {
                $childcategory = [
                    'Furniture'
                ];
                break;
            }
            case 'ArtAndCraftCategory' : {
                $childcategory = [
                    'ArtAndCraft'
                ];
                break;
            }
            case 'FoodAndBeverageCategory' : {
                $childcategory = [
                    'AlcoholicBeverages', 'FoodAndBeverage'
                ];
                break;
            }
            case 'ToolsAndHardware' : {
                $childcategory = [
                    'BuildingSupply', 'Electrical', 'Hardware', 'PlumbingAndHVAC', 'Tools', 'ToolsAndHardwareOther'
                ];
                break;
            }
            case 'SportAndRecreation' : {
                $childcategory = [
                    'Cycling', 'Weapons', 'Optics', 'SportAndRecreationOther'
                ];
                break;
            }
            case 'Photography' : {
                $childcategory = [
                    'CamerasAndLenses', 'PhotoAccessories'
                ];
                break;
            }
            case 'Animal' : {
                $childcategory = [
                    'AnimalHealthAndGrooming', 'AnimalAccessories', 'AnimalFood', 'AnimalEverythingElse'
                ];
                break;
            }
            case 'OccasionAndSeasonal' : {
                $childcategory = [
                    'Funeral', 'DecorationsAndFavors', 'CeremonialClothingAndAccessories', 'Costumes', 'GiftSupplyAndAwards'
                ];
                break;
            }
            case 'GardenAndPatioCategory' : {
                $childcategory = [
                    'GrillsAndOutdoorCooking', 'GardenAndPatio'
                ];
                break;
            }
            case 'FootwearCategory' : {
                $childcategory = [
                    'Footwear'
                ];
                break;
            }
            case 'Baby' : {
                $childcategory = [
                    'BabyFood', 'BabyOther', 'ChildCarSeats', 'BabyFurniture', 'BabyToys', 'BabyClothing'
                ];
                break;
            }
            case 'CarriersAndAccessoriesCategory' : {
                $childcategory = [
                    'CasesAndBags', 'CarriersAndAccessories'
                ];
                break;
            }
            case 'Media' : {
                $childcategory = [
                    'Movies', 'TVShows', 'Music', 'BooksAndMagazines'
                ];
                break;
            }
            case 'MusicalInstrument' : {
                $childcategory = [
                    'MusicCasesAndBags', 'SoundAndRecording', 'MusicalInstruments', 'InstrumentAccessories'
                ];
                break;
            }
            case 'OfficeCategory' : {
                $childcategory = [
                    'Office'
                ];
                break;
            }
            case 'OtherCategory' : {
                $childcategory = [
                    'Storage', 'giftCards', 'CleaningAndChemical', 'safetyAndEmergency', 'fuelsAndLubricants', 'Other'
                ];
                break;
            }
            case 'ToysCategory' : {
                $childcategory = [
                    'Toys'
                ];
                break;
            }
            case 'WatchesCategory' : {
                $childcategory = [
                    'Watches'
                ];
                break;
            }
            case 'Vehicle' : {
                $childcategory = [
                    'Tires', 'LandVehicles', 'VehiclePartsAndAccessories', 'WheelsAndWheelComponents', 'VehicleOther', 'Watercraft'
                ];
                break;
            }
            case 'ClothingCategory' : {
                $childcategory = [
                    'Clothing'
                ];
                break;
            }
            case 'JewelryCategory' : {
                $childcategory = [
                    'Jewelry'
                ];
                break;
            }
            case 'Electronics' : {
                $childcategory = [
                    'VideoGames', 'VideoProjectors', 'ElectronicsAccessories', 'ComputerComponents', 'ElectronicsCables', 'Software', 'Computers', 'TVsAndVideoDisplays', 'CellPhones', 'PrintersScannersAndImaging', 'ElectronicsOther'
                ];
                break;
            }
        }

        return $childcategory;
    }

    public static function getcategorytree()
    {

        $categorytree = [];
        $categorydetail = [];
        $root = self::getrootcategory();

        foreach ($root as $cat){

            $sub_category = self::getchildcategory($cat);
            foreach ($sub_category as $sub){

                $categorytree[$cat][$sub] =[];

                $categorydetail[$sub]=$sub;

            }

        }
        $return_arr[]=$categorytree;
        $return_arr[]=$categorydetail;
        return $return_arr;
    }

}