/**
 * Toggle the associated data when the Asignment Method is changed
 *
 * @param {String} sOption
 * @returns {Boolean}
 */
function toggleMethod(sOption)
{
  userDiv = document.getElementById('user');
  keyDiv = document.getElementById('key');
  levelDiv = document.getElementById('level');

  if (sOption === 'unassigned')
  {
    userDiv.style.display = keyDiv.style.display = levelDiv.style.display = 'none';
  }

  if (sOption === 'leasttickets' || sOption === 'roundrobin')
  {
    userDiv.style.display = 'none';
    keyDiv.style.display = levelDiv.style.display = 'block';
  }

  if (sOption === 'direct')
  {
    userDiv.style.display = 'block';
    keyDiv.style.display = levelDiv.style.display = 'none';
  }

  return false;
}


