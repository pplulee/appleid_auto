FROM python:3.11-slim

WORKDIR /app
ADD main.py /app
ADD requirements.txt /app
ADD lang.py /app

RUN pip install -r requirements.txt

ENV api_url=""
ENV api_key=""
ENV taskid=""
ENV lang=""

CMD python -u /app/main.py -api_url=$api_url -api_key=$api_key -taskid=$taskid -lang=$lang
