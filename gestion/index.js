const express = require("express");
const fileUpload = require("express-fileupload");
const pdfParse = require("pdf-parse");

const app = express();

app.use("/", express.static("public"));
app.use(fileUpload());

app.post("/extract-text", (req, res) => {
    if (!req.files || !req.files.pdfFile) {
        res.status(400).send("Aucun fichier reçu.");
        return;
    }

    pdfParse(req.files.pdfFile).then(result => {
        const extractedText = result.text;

        // Liste des mots-clés à chercher
        const keywords = ["Java", "Python", "HTML", "Licence", "Master", "Permis B"];

        const foundKeywords = keywords.filter(keyword => 
            new RegExp(`\\b${keyword}\\b`, "i").test(extractedText)
        );

        const response = foundKeywords.join("; ");

        res.send(response || "Aucun mot-clé trouvé.");
    }).catch(err => {
        res.status(500).send("Erreur lors de l'analyse du fichier PDF.");
        console.error(err);
    });
});

app.listen(3000);