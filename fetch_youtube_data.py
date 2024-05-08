import sys
import json
from googleapiclient.discovery import build
from googleapiclient.errors import HttpError
from pymongo import MongoClient




API_KEY = 'AIzaSyDrpH4D7DsFNLXAi_0VEhBg-I7T515oN2A'
youtube = build("youtube", "v3", developerKey=API_KEY)

def get_channel_info(channel_id):
    try:
        request = youtube.channels().list(
            part="snippet",
            id=channel_id
        )
        response = request.execute()
        if 'items' in response and len(response['items']) > 0:
            channel = response['items'][0]['snippet']
            channel_info = {
                "channel_id": channel_id,
                "title": channel['title'],
                "description": channel['description'],
                "published_at": channel['publishedAt']
            }
            return channel_info
        else:
            return {"error": "No channel found with the given ID."}
    except HttpError as e:
        return {"error": str(e)}

def get_latest_videos(channel_id, max_results):
    try:
        request = youtube.search().list(
            part="id,snippet",
            channelId=channel_id,
            maxResults=max_results,
            order="date"
        )
        response = request.execute()
        videos = []
        if 'items' in response:
            for item in response['items']:
                if 'videoId' in item['id']:
                    video = {
                        "video_id": item['id']['videoId'],
                        "title": item['snippet']['title'],
                        "description": item['snippet']['description']
                    }
                    videos.append(video)
        return videos
    except HttpError as e:
        return {"error": str(e)}
def getstat(vedioid):
    try:
        request = youtube.videos().list(
            part="statistics",
            id=vedioid
        )
        responce=request.execute()
        return responce['items'][0]['statistics']
    except Exception as e:
        pass

if __name__ == "__main__":
    if len(sys.argv) < 3:
        sys.exit(1)
    channel_id = sys.argv[1]
    d={}
    max_range = sys.argv[2]
    channel_info = get_channel_info(channel_id)
    latest_videos = get_latest_videos(channel_id,max_range)
    for i in latest_videos:
        data=getstat(i['video_id'])
        i['viewCount']=data['viewCount']
        i['likeCount']=data['likeCount']
        i['favoriteCount']=data['favoriteCount']
        i['commentCount']=data['commentCount']

    output = {
        "channel_info": channel_info,
        "latest_videos": latest_videos
    }
    client = MongoClient("mongodb://localhost:27017/")
    db = client['Youtube']
    collection = db['channel_info']
    collection.insert_one({
        "channel_id":output['channel_info']['channel_id'],
        "title":output['channel_info']['title'],
        "description":output['channel_info']['description'],
        "published_at":output['channel_info']['published_at']


    })
    collection = db[output['channel_info']['title']]
    for item in output['latest_videos']:
        collection.insert_one(
            {"video_id": item['video_id'],"title": item['title'],
             "description": item['description'],"viewCount":item['viewCount'],
             "likecounts":item['likeCount'],"favoriteCount":item['favoriteCount'],"commentCount":item['commentCount']}
        )

    for doc in db['channel_info'].find():
        if 'title' in doc and 'channel_id' in doc:
            d[doc['title']] = doc['channel_id']
    output['collectionlist']=d
    print(json.dumps(output, indent=2))
