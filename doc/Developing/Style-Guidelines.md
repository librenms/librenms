# Style guidelines

This document is here to help style standards for contributions towards LibreNMS. These aren't strict rules but it is in 
the users interest that a consistent well thought out Web UI is available.

### Responsiveness

The Web UI is designed to be mobile friendly and for the most part is and works well. It's worth spending sometime to 
read through the [Boostrap website](http://getbootstrap.com/css/#grid) to learn more about how to keep things responsive.

### Navigation bar

- Always pick the best location for new links to go, think about where users would expect the link to be located and name 
it so that it's obvious what it does.

- Ensure sub sections within the Navigation are separated correctly using `<li role="presentation" class="divider"></li>`.

- Only use [Font Awesome icons](http://fontawesome.io/icons/) within the Navigation. It speeds up page load times quite 
considerably.

### Buttons

Try to keep buttons colored to reflect the action they will take. Buttons are set using Bootstrap classes. The size of 
the buttons will depend on the area of the website being used but btn-sm is probably the most common.

- Delete / Remove buttons: btn btn-danger

- Edit / Update buttons: btn btn-primary

- Add / Create buttons: btn btn-success

### Tables

Unless the table being used will only ever display a handful of items - yeah that's what we all said, then you need to 
write your table using [JQuery Bootgrid](http://www.jquery-bootgrid.com/). This shouldn't take that much more code to 
do this but provides so much flexibility along with stopping the need for retrieving all the data from SQL in the first 
place.

As an example pull request, see [PR 706](https://github.com/librenms/librenms/pull/706/files) to get an idea of what 
it's like to convert an existing pure html table to Bootgrid.