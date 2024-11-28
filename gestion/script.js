
function showInfo(infoId) {

    document.getElementById('defaultInfo').style.display = 'none';
    document.getElementById('societeInfo').style.display = 'none';

    document.getElementById(infoId).style.display = 'block';
}

function hideInfo() {

    document.getElementById('defaultInfo').style.display = 'block';
    document.getElementById('societeInfo').style.display = 'block';
    
    document.getElementById('infoPostuler').style.display = 'none';
    document.getElementById('infoChatbot').style.display = 'none';
    document.getElementById('infoTransformation').style.display= 'none';
    document.getElementById('infoTestTechnique').style.display= 'none';
    document.getElementById('infoNotifications').style.display = 'none';
    document.getElementById('infoCompatibilite').style.display = 'none';
    document.getElementById('infoDashboard').style.display = 'none';
    document.getElementById('infoGestionPaie').style.display = 'none';
    
    
}

window.onload = function() {
    document.getElementById('defaultInfo').style.display = 'block';
    document.getElementById('societeInfo').style.display = 'block';
};