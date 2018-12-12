<?php

function getDataUri($path)
{
    return 'data:application/pdf;base64,' . base64_encode(file_get_contents($path));
}

$simple = isset($_GET['simple']) && $_GET['simple'];

if (!$simple):
    ?>

    <button id="changeLayerBtn">change layer</button>
    <script>
        window.addEventListener('load', function() {
            var first = document.getElementById('first');
            var second = document.getElementById('second');

            first.style.zIndex = '0';
            first.style.opacity = '1';

            second.style.zIndex = '1';
            second.style.opacity = '0.2';

            var button = document.getElementById('changeLayerBtn');

            var buttonText = button.innerText;

            button.innerText = buttonText + " current: 1";

            button.addEventListener('click', function () {
                var name = '';

                if (first.style.opacity == '1') {
                    first.style.opacity = '0.2';
                    second.style.opacity = '1';
                    first.style.zIndex = '2';
                    name = '0';
                } else {
                    first.style.zIndex = '0';
                    first.style.opacity = '1';
                    second.style.opacity = '0.2';
                    name = '1';
                }

                button.innerText = buttonText + " current: " + name;
            });
        });
    </script>
    <div style="position: relative; width: 49%; height: 90%; float: left;" id="container">
        <div style="position: absolute;top: 1px; left: 1px; width: 100%; height:100%;" id="first">
            <embed src="<?= getDataUri(__DIR__ . '/pdfs/1.pdf') ?>" width="100%" height="100%"/>
        </div>
        <div style="opacity: 0.2; position: absolute;top: 1px; left: 1px; width: 100%; height:100%;" id="second">
            <embed src="<?= getDataUri(__DIR__ . '/pdfs/0.pdf') ?>" width="100%" height="100%"/>
        </div>
    </div>
    <iframe style="width: 49%" height="90%" src="http://pdfanalyzer2.dev1.setasign.local/?file=<?=realpath(__DIR__ . '/pdfs/1.pdf')?>"></iframe>

<?php else: ?>
    <embed src="<?= getDataUri(__DIR__ . '/pdfs/1.pdf') ?>" width="49%" height="90%"/>
    <embed src="<?= getDataUri(__DIR__ . '/pdfs/0.pdf') ?>" width="49%" height="90%"/>
<?php endif; ?>
