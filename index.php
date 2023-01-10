<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Spring Flowers</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>

</head>

<?php

// function returns row by id value
function SearchForId($id, $array)
{
    foreach ($array as $val) {
        if ($val['productId'] === $id) {
            return $val;
        }
    }
    return null;
}


// function returns index by id value.....
function SearchForIdGetIndex($id, $array)
{
    $index = 0;
    foreach ($array as $val) {
        if ($val['productId'] === $id) {
            return $index;
        }
        $index++;
    }
    return null;
}


///////////////////////////////


// getting the parameters id if it is found, sanitize and validate
$category = filter_input(INPUT_GET, "category", FILTER_SANITIZE_SPECIAL_CHARS); // category name
$SpecificProductID = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT); // to shows otem in card
$list = filter_input(INPUT_GET, "list", FILTER_SANITIZE_NUMBER_INT);            //list number 
$addToCartID = filter_input(INPUT_POST, "addToCartID", FILTER_SANITIZE_NUMBER_INT); // product id to add it to cart
$deleteToCartID = filter_input(INPUT_GET, "deleteToCartID", FILTER_SANITIZE_NUMBER_INT); // product id to delete it from cart
$cartList = array(); // cart list products

if (!isset($category) and !isset($SpecificProductID)) {
    if (isset($_SESSION["category"])) {
        $category =  $_SESSION["category"];
    } else {
        $_SESSION["category"] = "";
        $category =  "";
    }
} else if (!isset($category) and isset($SpecificProductID)) {
    $category = $_SESSION["category"];
} else if (isset($category) and !isset($SpecificProductID)) {
    if (
        $category === "Shrubs" or
        $category === "Container Plants" or
        $category === "Herbaceous Perennials" or
        $category === "Cacti and Succulents" or
        $category === ""
    ) {
        $_SESSION["category"] = $category;
    }
} else {
    $category = $_SESSION["category"];
}


////////////////////

if (!isset($_SESSION["data"])) {

    //read json file and save it in $data
    $filename = 'flowers.json';
    if (file_exists($filename)) {
        $myfile = fopen($filename, "r") or die("Unable to open file!");
        $filedata = file_get_contents($filename);
        $data = json_decode($filedata, true);
        fclose($myfile);
    } else {
        print "Data file not created!";
    }

    $_SESSION["data"] = $data;
} else {

    //save data in $data from SESSION
    $data = $_SESSION["data"];
}

/////////////////////

if (isset($_SESSION["cartList"])) {
    $cartList = $_SESSION["cartList"];
}

/////// add to cart 
if (isset($addToCartID)) {

    $product = SearchForId((int)$addToCartID, $data);
    array_push($cartList, $product); // add it to cartList



    //minimize -1 in the main products list
    $index = SearchForIdGetIndex((int)$addToCartID, $data);
    $data[$index]["quantity"] -= 1;

    $_SESSION["data"] = $data;
    $_SESSION["cartList"] = $cartList;
}


/////// delete from cart

if (isset($deleteToCartID)) {
    $product = SearchForId((int)$deleteToCartID, $data);

    //delete from cartList
    $index1 = SearchForIdGetIndex((int)$deleteToCartID, $cartList);
    unset($cartList[$index1]);

    //maximize -1 in the main products list
    $index2 = SearchForIdGetIndex((int)$deleteToCartID, $data);
    $data[$index2]["quantity"] += 1;

    $_SESSION["data"] = $data;
    $_SESSION["cartList"] = $cartList;
}

?>





<!-- ///////////////////////////// start html script//////////////////////////////////// -->

<body class="d-flex flex-column min-vh-100">
    <div class="container">

        <div class="header row">
            <p class="col-10 my-custom-box">Spring Flowers</p>
            <img src="img/logo.jpg" alt="Spring Flowers logo" class="col-2 align-self-center">
        </div>

        <div class="row mt-4">


            <!-- ////////////////////////////////////// categories ////////////////////////////////////// -->
            <div class="col-2 p-0 m-0">
                <div class="list-group my-custom-box">
                    <h4 class="text-center">Product categories</h4>

                    <a href="index.php?category=Shrubs" class="list-group-item list-group-item-action <?php if ($category === "Shrubs") {
                                                                                                            echo " active";
                                                                                                        } ?>">Shrubs</a>
                    <a href="index.php?category=Container Plants" class="list-group-item list-group-item-action <?php if ($category === "Container Plants") {
                                                                                                                    echo " active";
                                                                                                                } ?>">Container Plants</a> <!-- active -->
                    <a href="index.php?category=Herbaceous Perennials" class="list-group-item list-group-item-action <?php if ($category === "Herbaceous Perennials") {
                                                                                                                            echo " active";
                                                                                                                        } ?>">Herbaceous Perennials</a>
                    <a href="index.php?category=Cacti and Succulents" class="list-group-item list-group-item-action <?php if ($category === "Cacti and Succulents") {
                                                                                                                        echo " active";
                                                                                                                    } ?>">Cacti & Succulents</a>

                    <a href="index.php?category=" class="list-group-item list-group-item-action <?php if ($category === "") {
                                                                                                    echo " active";
                                                                                                } ?>">Everything</a>

                </div>
            </div>



            <!-- ///////////////////////////////////////////////// catalog ///////////////////////////////////////////// -->
            <div class="col-7">
                <?php
                if ($SpecificProductID === null) {
                ?>

                    <table class="table table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Product ID</th>
                                <th scope="col">Product Name</th>
                                <th scope="col">Price</th>
                                <th scope="col">In Stock</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php

                            $flowersList = array();
                            $everyhting = false;
                            if ($category === "") {
                                $everyhting = true;
                            }

                            foreach ($data as $flower) {
                                if ($flower["category"] === $category or $everyhting) {
                                    array_push($flowersList, $flower);
                                }
                            }

                            $flowersListCount = count($flowersList);

                            if ($list === null or $list === "" or $list < 1) {
                                $list = 1;
                            }

                            $flowersList = array_slice($flowersList, ($list * 7) - 7, 7);

                            foreach ($flowersList as $flower) {
                            ?>

                                <tr>
                                    <th scope="row"><?php echo $flower["productId"]; ?></th>
                                    <td>
                                        <a href="index.php?id=<?php echo $flower["productId"]; ?>">
                                            <?php echo $flower["name"]; ?>
                                        </a>
                                    </td>
                                    <td><?php echo "$" . $flower["price"]; ?></td>
                                    <td>
                                        <?php if ($flower["quantity"] === 0) {
                                            echo "Out of Stock";
                                        } else {
                                            echo $flower["quantity"];
                                        }
                                        ?>
                                    </td>
                                </tr>


                            <?php } ?>

                        </tbody>
                    </table>
                    <div class="row">

                        <a class='col-3<?php if ((int)$list === 1) echo " arrow-disabled"; ?>' href="index.php?list=<?php echo $list - 1; ?>">
                            <img class="arrow-img arrowprevious" src="img/arrowprevious.png">
                        </a>
                        <p class="col-6 list-count-lable">
                            <?php echo $list . " - " . ceil($flowersListCount / 7); ?>
                        </p>
                        <a class='col-3<?php if ($flowersListCount <= $list * 7) echo " arrow-disabled"; ?>' href="index.php?list=<?php echo $list + 1; ?>">
                            <img class="arrow-img arrownext" src="img/arrownext.png">
                        </a>
                    </div>

                <?php
                } else {

                    $cardData = SearchForId((int)$SpecificProductID, $data);

                    $imageURL = 'img/flowers/' . $cardData["productId"] . '.jpg';

                ?>

                    <form action="index.php" method="POST">
                        <div class="card">
                            <div class="top-right">
                                <a href="index.php">X</a>
                            </div>

                            <img <?php
                                    if (file_exists($imageURL)) {
                                        echo "src='" . $imageURL . "'";
                                    } else {
                                        echo "src='img/noImage.png'";
                                    }
                                    ?> alt="flower image">

                            <h3><?php echo $cardData["name"] ?></h3>
                            <p class="price"><?php echo "$" . $cardData["price"]; ?></p>
                            <p>Product Code: <?php echo $cardData["productId"]; ?></p>
                            <p><?php echo $cardData["description"]; ?></p>

                            <input type="text" name="addToCartID" value="<?php echo $cardData["productId"]; ?>" hidden>

                            <input type="submit" class="button-add" <?php if ($cardData["quantity"] === 0) {
                                                                        echo " value='Out of Stock' disabled";
                                                                    } else {
                                                                        echo " value='Add to Cart'";
                                                                    } ?>>
                        </div>
                    </form>

                <?php } ?>
            </div>



            <!-- ////////////////////cart////////////////////// -->
            <div class="col-3 p-0">

                <div class="list-group my-custom-box">
                    <h4 class="text-center">Shopping Cart</h4>
                    <hr>
                    <?php
                    $total = 0;
                    foreach ($cartList as $item) {
                        $total += $item["price"];
                    ?>

                        <div class="row cart">
                            <div class="col-7">
                                <?php echo $item["name"] . ", ID(" . $item["productId"] . ")"; ?>
                            </div>
                            <div class="col-3">
                                $<?php echo $item["price"]; ?>
                            </div>
                            <div class="col-1">
                                <a href="index.php?deleteToCartID=<?php echo $item["productId"]; ?>">
                                    X
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                    <hr>
                    <label class="text-center font-weight-bold">Total: $<?php echo $total ?> </label>
                    <input type="submit" class="button-add" name="" value="Checkout">
                </div>
            </div>

        </div>


        <!-- /////////////footer//////////// -->
        <div class="row mt-4">
            <div>
                <hr>
                <p class="text-center">This website made by George Hady - 000821026</p>
            </div>
        </div>

    </div>
</body>

</html>