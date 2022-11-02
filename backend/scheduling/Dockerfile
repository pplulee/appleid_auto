FROM python:3.10.8-alpine
RUN apk add --no-cache docker-cli

WORKDIR /app
ADD unblocker_manager.py /app
ADD requirements.txt /app

RUN pip install -r requirements.txt

VOLUME [ "/var/run/docker.sock" ]

CMD python -u /app/unblocker_manager.py -api_url=$api_url -api_key=$api_key