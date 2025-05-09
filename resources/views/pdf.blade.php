<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="{{route('makePDF')}}" method="post">
        @csrf
        <textarea name="pdf_content"></textarea>
        <button type="submit"> Generate </button>
    </form>

    <form action="{{route('hashpdf')}}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file"></input>
        <button type="submit"> sign </button>
    </form>
</body>
</html>