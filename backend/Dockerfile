FROM python:3.11-alpine

WORKDIR /app
ADD unblocker_manager_docker.py /app
ADD requirements.txt /app

RUN pip install -r requirements.txt

ENV API_URL=""
ENV API_KEY=""
ENV SYNC_TIME=10
ENV LANG=1
ENV AUTO_UPDATE=False

CMD if [ "${AUTO_UPDATE}" = "True" ]; then \
        python -u unblocker_manager_docker.py -api_url=$API_URL -api_key=$API_KEY -sync_time=$SYNC_TIME -lang=$LANG -auto_update; \
    else \
        python -u unblocker_manager_docker.py -api_url=$API_URL -api_key=$API_KEY -sync_time=$SYNC_TIME -lang=$LANG; \
    fi
