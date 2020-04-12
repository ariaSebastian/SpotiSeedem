$(window).scroll(function () {
    let offset = releases.offset + releases.limit;
    if (offset < releases.total) {
        let hT = $('#divLoading').offset().top,
            hH = $('#divLoading').outerHeight(),
            wH = $(window).height(),
            wS = $(this).scrollTop();
        if (wS > (hT + hH - wH) && !$('#divLoading div').is(':visible')) {
            $('#divLoading div').fadeIn('slow');
            pagingReleases(offset);
        }
    } else {
        $('#divLoading div').html('<h4>No hay mas resultados</h4>').addClass('border-bottom').fadeIn('slow');
    }
});

function pagingReleases(offset) {
    fetch(Routing.generate('home_paginated_releases', {'offset': offset}))
        .then(function (response) {
            if (response.ok) {
                return response.json();
            } else {
                console.log('EndpointError: pagingReleases', response);
                alert('EndpointError');
            }
        })
        .then(function (data) {
            // console.log(data);
            releases = data;
            $('#divLoading div').fadeOut('slow');
            releasesLoad();
        })
        .catch(function (error) {
            console.log('FetchError: pagingReleases', error);
            alert('FetchError');
        });
}

function releasesLoad() {
    let html = '';
    Object.entries(releases.items).forEach(([index, release]) => {
        html += htmlCarouselItem(parseInt(index), release);
    });

    $('#divReleasesLoad').append(html);
}

function htmlCarouselItem(index, release) {
    let html = '';
    let artistUrl = '';

    if (index % 5 == 0) {
        html += '<div class="row carousel__container">';
    }

    html += `<div class="carousel-item">` +
        `<img src="${release.images ? release.images[0].url : imgNoAvailable}"` +
        `class="carousel-item__img" alt="Album image"/>` +
        `<div class="carousel-item__details">` +
        `<h6 class="font-weight-bold carousel-item__details--title">${release.name}</h6>` +
        `<p class="carousel-item__details--subtitle">`;

    Object.entries(release.artists).forEach(([idx, artist]) => {
        artistUrl = Routing.generate('home_artist', {'id': artist.id});
        html += `<a href="${artistUrl}" class="badge badge-primary mr-2">${artist.name}</a>`;
    });

    html += `</p>` +
        `</div>` +
        `</div>`;

    if ((index + 1) % 5 == 0) {
        html += '</div>';
    }

    return html;
}