<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $title ?? 'Error' }} | {{ $fillSet ??  'Filler Images' }} by LucidInternets</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="The Best Motherfucking Website">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans">

    @isset($refresh)
      <meta http-equiv="refresh" content="4; url={{ $refresh }}" />
    @endisset


    <style>
      html {
          background-color: #fefefe;
      }
      body {
          font-family: Open Sans,Arial;
          color: #050505;
          font-size: 16px;
          margin: 2em auto;
          max-width: 800px;
          padding: 1em;
          line-height: 1.4;
          text-align: justify;
      }
      @media screen and (max-width:500px) {
          body {
              text-align: left;
          }
      }
    </style>
</head>

<body>
    <h1>{{ $title }}</h1>
    <p>{!! $message ?? 'Something went wrong with the request.' !!}</p>
</body>

</html>
