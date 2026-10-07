<?php

    // Get the form fields, removes html tags and whitespace.
    $name = strip_tags(trim(($_POST["name"] ?? "")));
    $name = str_replace(array("\r","\n"),array(" "," "),$name);
    $email = filter_var(trim(($_POST["email"] ?? "")), FILTER_SANITIZE_EMAIL);
    $message = trim(($_POST["message"] ?? ""));
    
    // CHECK FOR BOTS SUBMITTING AND FOR THAT SPECIFIC SPAM EMAIL ADDRESS
    $blank = trim(($_POST["egghead"] ?? ""));
    $reject = "1milliondollars.xyz";
    $splitarray = explode('@', $email);

    // Check the data.
    if (empty($name) OR empty($message) OR !filter_var($email, FILTER_VALIDATE_EMAIL) OR !empty($blank) OR strcasecmp( ($splitarray[1] ?? ""), $reject ) === 0) {
        header("Location: index.php?success=-1#scroll-tab-contact");
        echo("something went wrong");
        exit;
    }

    // Set the recipient email address. Update this to YOUR desired email address.
    $recipient = "ian@ianposton.com";

    // Set the email subject.
    $subject = "New contact from $name";

    // Build the email content.
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Message:\n$message\n";

    // Build the email headers.
    $email_headers = "From: $name <$email>";

    // Send the email.
    $sent = mail($recipient, $subject, $email_content, $email_headers);
    
    // Redirect to the index.html page with success code
    header("Location: index.php?success=" . ($sent ? "1" : "-1") . "#scroll-tab-contact");
    exit;

?>