<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    //     $categories=[
    //      'Men'=>['Shirts','Pants','Shoes'],
    //      'Woman'=>['Shirts','Pants','Shoes'],
    //      'Kids'=>['Shirts','Pants','Shoes']
    //     ];

    //     foreach($categories as $parentName=>$children){
    //      $parent=Category::create([
    //        'name'=>$parentName,
    //        'parent_id'=>null
    //      ]);
        
    //     foreach($children as $childrenName){
    //         Category::create([
    //             'name'=>$childrenName,
    //              'parent_id'=>$parent->id
    //         ]);
    //     }
    // }
     $categories = [
    'Men' => [
            'Clothing' => ['Shirts', 'Pants', 'Shoes'],
            'Accessories' => ['Watches', 'Bags', 'Hats']
        ],

        'Women' => [
            'Fashion' => ['Dresses', 'Shoes', 'Bags'],
            'Beauty' => ['Makeup', 'Skincare']
        ],

        'Kids' => [
            'Clothing' => ['Shirts', 'Pants', 'Shoes'],
            'Toys' => ['Educational', 'Outdoor']
        ]

        
    ];
  foreach($categories as $parentName=>$subcategories){
    $parent=Category::create([
     'name'=>$parentName,
     'parent_id'=>null
    ]);
    foreach($subcategories as $childName=>$childItems){
        if(is_array($childItems)){
     $child=Category::create([
      'name'=>$childName,
      'parent_id'=>$parent->id
     ]);
    foreach($childItems as $subchildName=>$value){
        if(is_array($value)){
            $subchild=Category::create([
             'name'=>$subchildName,
             'parent_id'=>$child->id
            ]);
            foreach($value as $lastlevel){
                Category::create([
                    'name'=>$lastlevel,
                    'parent_id'=>$subchild->id
                ]);
            }
        }else{
            Category::create([
                'name'=>$value,
                'parent_id'=> $child->id
        ]);
        }
    }
     
    }
    else{
        Category::create([
            'name'=>$childItems,
            'parent_id'=>$parent->id
        ]);
    }
  }
}

    }
}












































