@php
    $allModules = Module::all();
    $activeModules = [];

    function moveUniversalToFront($array, $keyword = 'Universal') {
            // Find the index of the item with the specified keyword in the product_name
            $index = array_search(true, array_map(function ($item) use ($keyword) {
                return stripos($item['product_name'], $keyword) !== false;
            }, $array));

            // If the item is found, move it to the first position
            if ($index !== false) {
                $item = $array[$index];
                unset($array[$index]);
                array_unshift($array, $item);
            }

            return $array;
    }

     $universal = false;

    foreach ($allModules as $module) {

         $config = require base_path() . '/Modules/' . $module . '/Config/config.php';

          if(isset($config['envato_item_id']) && $config['envato_item_id']!== ''){
                if(stripos($config['name'], 'universal') !== false){
                    $universal = true;
                    break;
                }
                $activeModules[] = $config['envato_item_id'];
          }
    }

     $notInstalledModules = [];

     if(!$universal){
         $plugins = \Froiden\Envato\Functions\EnvatoUpdate::plugins();

        if (empty($plugins)) {
            $plugins = [];
        }else{
            $plugins = moveUniversalToFront($plugins);
        }

        foreach ($plugins as $item) {
            if (!in_array($item['envato_id'], $activeModules)) {
                $notInstalledModules[] = $item;
            }
        }
     }


@endphp

@if (count($notInstalledModules) && !$universal)
    <style>

        .rainbow {
            position: relative;
            z-index: 0;
            overflow: hidden;
            padding: 2rem;

        &
        ::before {
            content: '';
            position: absolute;
            z-index: -2;
            left: -50%;
            top: -50%;
            width: 200%;
            height: 200%;
            background-color: #399953;
            background-repeat: no-repeat;
            background-size: 50% 50%, 50% 50%;
            background-position: 0 0, 100% 0, 100% 100%, 0 100%;
            background-image: linear-gradient(#399953, #399953), linear-gradient(#fbb300, #fbb300), linear-gradient(#d53e33, #d53e33), linear-gradient(#377af5, #377af5);
            animation: rotate 4s linear infinite;
        }

        &
        ::after {
            content: '';
            position: absolute;
            z-index: -1;
            left: 3px;
            top: 3px;
            width: calc(100% - 6px);
            height: calc(100% - 6px);
            background: white;
            border-radius: 2px;
        }

        }
    </style>
    <div class="col-sm-12 mt-5">
        <h4> ماژول های در حال توسعه </h4>
           
                        
          
    </div>
@endif
