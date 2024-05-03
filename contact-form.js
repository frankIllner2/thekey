jQuery(document).ready(function($) {
    $('#contact-form').submit(function(e) {
        e.preventDefault(); // Verhindere das Standard-Formularverhalten
        
        // Erfasse die Formulardaten
        let formData = {};
        formData.fullname = $(this).parent().find('#fullname').val();
        formData.mail = $(this).parent().find('#mail').val();
        formData.subject = $(this).parent().find('#subject').val();
        formData.message = $(this).parent().find('#message').val();
        formData.submit = $(this).parent().find('#submit').val();
     
        // Sende die Daten per Ajax
        $.ajax({
            method: "POST",
            dataType: 'json',
            url: ajax_object.ajax_url, // Der URL-Endpunkt, der die Daten verarbeitet
            data: {
                action: 'process_contact_form',
                formData: formData
            },
            success: function() { },
            complete: function () {
                // Bestätigungsnachricht ausgeben
                jQuery("#contact-form").prepend('<div class="success">Ihre Daten wurden erfolgreich gespeichert!</div>');
                // Message ausbelnden
                setTimeout(function(){ 
                    jQuery(".success").fadeOut("slow");
                },3000);
            }
            
        });
      
    });
});