<?php
    include_once("conn.php");

    $method = $_SERVER["REQUEST_METHOD"];
    
    // Resgate dos dados, montagem do pedido
    if ($method == "GET") {

        $bordasQuery = $conn->query("SELECT * FROM bordas");

        $bordas = $bordasQuery->fetchAll();

        $massasQuery = $conn->query("SELECT * FROM massas");

        $massas = $massasQuery->fetchAll();

        $saboresQuery = $conn->query("SELECT * FROM sabores");

        $sabores = $saboresQuery->fetchAll();

    // Criação do pedido
    } else if ($method == "POST") {

        $data = $_POST;

        $borda = $data["borda"];
        $massa = $data["massa"];
        $sabores = $data["sabores"];

        // Validação de sabores máximos
        if(count($sabores) > 3) {

            $_SESSION["msg"] = "Selecione no máximo 3 sabores";
            $_SESSION["status"] = "warning";

        } else {

            // Salvando borda e massa na pizza

            $stmt = $conn->prepare("INSERT INTO pizzas (borda_id, massa_id) VALUES (:borda, :massa)");

            //Filtrando inputs

            $stmt->bindParam(":borda", $borda, PDO::PARAM_INT);
            $stmt->bindParam(":massa", $massa, PDO::PARAM_INT);

            $stmt->execute();

            // Resgatando ultimo id da ultima pizza

            $pizzaID = $conn->lastInsertId();

            $stmt = $conn->prepare("INSERT INTO pizza_sabor (pizza_id, sabor_id) VALUES (:pizza, :sabor)");

            // Repetição até terminar de salvar todos os sabores
            foreach ($sabores as $sabor){

            // Filtrando os inputs

            $stmt->bindParam(":pizza", $pizzaID, PDO::PARAM_INT);
            $stmt->bindParam(":sabor", $sabor, PDO::PARAM_INT);

            $stmt->execute();

            }

            // Criar o pedido da pizza
            $stmt = $conn->prepare("INSERT INTO pedidos (pizza_id, status_id) VALUES (:pizza, :status)");

            // status -> sempre inicia com 1, que é em produção
            $statusID = 1;

            // Filtrar inputs
            $stmt->bindParam(":pizza", $pizzaID, PDO::PARAM_INT);
            $stmt->bindParam(":status", $statusID, PDO::PARAM_INT);

            $stmt->execute();

            // Excibir mensagem de sucesso

            $_SESSION["msg"] = "Pedido Realizado com sucesso!";
            $_SESSION["status"] = "Success";

        }

        // Retorna para página inicial
        header("Location: ..");

    } 
?>