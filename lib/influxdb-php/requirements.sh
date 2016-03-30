#!/bin/bash

#!/bin/bash

GLEXEC=$(which ansible-galaxy)

if [ $? = 1 ]; then
  echo
  echo "Ansible-galaxy not found, please make sure you have ansible 1.9+ installed."
  echo
  exit 1
fi

$GLEXEC install -f -r ansible/requirements.txt
