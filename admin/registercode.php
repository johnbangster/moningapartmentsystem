
<?php 
session_start();
include('config/dbcon.php');


if (isset($_POST['register_btn'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);

    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['cpass'];
    $role = $_POST['role'];

    // Server-side validations
    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords does not match.";
        header("Location: register.php");
        exit(0);
    }

    if (!preg_match("/^\d{10,12}$/", $contact)) {
        $_SESSION['message'] = "Contact number must be 10 to 12 digits.";
        header("Location: register.php");
        exit(0);
    }

    // Check if email already exists
    $check_query = "SELECT * FROM users WHERE email = ?";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Email already registered.";
        header("Location: register.php");
        exit(0);
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into DB
    $insert_query = "INSERT INTO users (first_name, last_name, middle_name, contact, email, address, password, role, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?,?, 'Active')";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("ssssssss", $first_name, $last_name, $middle_name , $contact, $email, $address, $hashed_password, $role);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Registered successfully!";
        header("Location: register.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Registration failed.";
        header("Location: register.php");
        exit(0);
    }

} else {
    $_SESSION['message'] = "Invalid request.";
    header("Location: register.php");
    exit(0);
}


// if (isset($_POST['register_btn'])) {
//     $fname        = mysqli_real_escape_string($con, $_POST['fname']);
//     $lname        = mysqli_real_escape_string($con, $_POST['lname']);
//     $middle_name  = mysqli_real_escape_string($con, $_POST['middle_name']);
//     $contact      = mysqli_real_escape_string($con, $_POST['contact']);
//     $email        = mysqli_real_escape_string($con, $_POST['email']);
//     $address      = mysqli_real_escape_string($con, $_POST['address']);
//     $password     = mysqli_real_escape_string($con, $_POST['password']);
//     $cpass        = mysqli_real_escape_string($con, $_POST['cpass']);
//     $role         = mysqli_real_escape_string($con, $_POST['role']);

//     if ($password !== $cpass) {
//         $_SESSION['message'] = "Passwords do not match!";
//         header("Location: ../login.php");
//         exit();
//     }

//     // Check if contact or email already exists
//     $check = mysqli_query($con, "SELECT * FROM users WHERE email='$email' OR contact='$contact'");
//     if (mysqli_num_rows($check) > 0) {
//         $_SESSION['message'] = "Email or Contact already exists!";
//         header("Location: ../login.php");
//         exit();
//     }

//     // Hash the password
//     $hashed = password_hash($password, PASSWORD_DEFAULT);

//     // Insert new user
//     $sql = "INSERT INTO users 
//             (first_name, last_name, middle_name, contact, email, address, password, role)
//             VALUES 
//             ('$fname', '$lname', '$middle_name', '$contact', '$email', '$address', '$hashed', '$role')";

//     if (mysqli_query($con, $sql)) {
//         $_SESSION['message'] = "User registered successfully!";
//     } else {
//         $_SESSION['message'] = "Registration failed: " . mysqli_error($con);
//     }

//     header("Location: ../login.php");
//     exit();
// }


// if(isset($_POST['register_btn']))
// {
//     $fname = mysqli_real_escape_string($con, $_POST['fname']);
//     $lname = mysqli_real_escape_string($con, $_POST['lname']);
//     $middle_name = mysqli_real_escape_string($con, $_POST['middle_name']);
//     $contact = mysqli_real_escape_string($con, $_POST['contact']);
//     $email = mysqli_real_escape_string($con, $_POST['email']);
//     $address = mysqli_real_escape_string($con, $_POST['address']);
//     $password = mysqli_real_escape_string($con, $_POST['password']);
//     $cpass = mysqli_real_escape_string($con, $_POST['cpass']);

//     if($password == $cpass)
//     {
//         //check email if already exists
//         $checkEmail = "SELECT email FROM users WHERE email='$email'";
//         $checkEmail_run = mysqli_query($con, $checkEmail);

//         if(mysqli_num_rows($checkEmail_run) > 0)
//         {
//             $_SESSION['message'] = "Email already exists";
//             header("Location: register.php");
//             exit(0);
//         }
//         else
//         {
//             $user_query = "INSERT INTO users (fname,lname,middle_name,contact,email,address,password) VALUES ('$fname', '$lname', '$middle_name', '$contact', '$email', '$address', '$password')";
//             $query_run = mysqli_query($con,$user_query);
            
//             if($query_run)
//             {
//                 $_SESSION['message'] = "Registered Successfully!";
//                 header("Location: ../login.php");
//                 exit(0);
//             }
//             else
//             {
//                 $_SESSION['message'] = "Unable to register, something went wrong!";
//                 header("Location: register.php");
//                 exit(0);
//             }
//         }
//     }
//     else
//     {
//         $_SESSION['message'] = "Password and Confirm Password does not match!";
//         header("Location: register.php");
//         exit(0);
//     }


// }
// else
// {
//     header("Location: register.php");
//     exit(0);
// }



?>