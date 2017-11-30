<!-- I used Open-source video player VideoJS for the player http://videojs.com/-->


<style>


    .video-js{
        margin: 5% 20%;
    }

</style>

<head>
    <link href="http://vjs.zencdn.net/vjs-version/video-js.css" rel="stylesheet">

    <!-- If you'd like to support IE8 -->
    <script src="http://vjs.zencdn.net/ie8/ie8-version/videojs-ie8.min.js"></script>
</head>

<body>
<video id="my-video" class="video-js" controls preload="auto" width="640" height="264" poster="{{$imagepath}}" data-setup="{}">
    <source src="{{$videopath}}" type='video/mp4'>
    <p class="vjs-no-js">
        To view this video please enable JavaScript, and consider upgrading to a web browser that
        <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
    </p>
</video>

<script src="http://vjs.zencdn.net/vjs-version/video.js"></script>
</body>