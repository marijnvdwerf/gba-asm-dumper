<!doctype html>
<html>
<head>
    <style>
        * {
            box-sizing: border-box;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        td, th {
            padding: 0;
        }

        td {
            border: 1px solid red;
        }

        .blob-num {
            width: 1%;
            min-width: 50px;
            padding-right: 10px;
            padding-left: 10px;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 12px;
            line-height: 20px;
            color: rgba(27, 31, 35, 0.3);
            text-align: right;
            white-space: nowrap;
            vertical-align: top;
            cursor: pointer;
           /* -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none*/
        }

        .blob-num::before {
            content: attr(data-line-number)
        }

        .blob-code {
            position: relative;
            padding-right: 10px;
            padding-left: 10px;
            line-height: 20px;
            vertical-align: top
        }

        .blob-code-inner {
            overflow: visible;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 12px;
            color: #24292e;
            word-wrap: normal;
            white-space: pre
        }

        .error {
            background: orangered;
            color: #fff;
        }
    </style>
</head>
<body>
