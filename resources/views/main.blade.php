<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous">
</script>
<body>
    <button id='generate-btn'> Generate keys </button>
    <form id="contract_form_enc">
        <input type="submit" value="encrypt" id="click-btn-encrypt">
    </form>
    <pre id="encryption-hash"></pre>
    <pre id="encryption-code"></pre>
    <form id="contract_form_dec">
        <input type="submit" value="decrypt" id="click-btn-decrypt">
    </form>
    <pre id="decryption-code"></pre>
    <pre id="decryption-code-received"></pre>
    <pre id='msg'></pre>
</body>
<script>
    $('#generate-btn').on('click',function(e){
        $.ajax({
        url: "{{route('document.generate')}}",
        method: 'POST',
        headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
    })
    
    $('#click-btn-encrypt').on('click',function (e) {
        e.preventDefault();
        $.ajax({
            url:"{{route('document.encrypt')}}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            xhrFields: {
        withCredentials: true  // Make sure cookies are sent with the request
    }
        }).done(function (resp) {
            $('#encryption-code').html(`Encryption: ${resp.ciphertext}`);
            $('#encryption-hash').html(`The hash of file is :${resp.hash}`);
        })
    })

    $('#click-btn-decrypt').on('click',function (e) {
        e.preventDefault();
        $.ajax({
            url:"{{route('document.decrypt')}}",
            method: 'POST',
            headers: {
        'x-csrf-token': $('meta[name="csrf-token"]').attr('content')
    },
        }).done(function (resp) {
            $('#decryption-code').html(`The hash of file is: ${resp.plaintext}`);
            $('#decryption-code-received').html(`The received hash is: ${resp.decryptedMsgHash}`);
            $('#msg').html(`${resp.msg}`);
        })
    })
</script>
<style>
    #encryption-code {
    word-wrap: break-word; /* Break words if necessary */
    white-space: pre-wrap; /* Preserve new lines and wrap long text */
    overflow-wrap: break-word; /* Ensure long words break properly */
}
</style>
</html>