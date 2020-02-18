import edu.stanford.nlp.ie.crf.*;

import edu.stanford.nlp.ie.*;
import edu.stanford.nlp.io.IOUtils;
import edu.stanford.nlp.io.RuntimeIOException;
import edu.stanford.nlp.ling.CoreAnnotations;
import edu.stanford.nlp.ling.CoreLabel;
import edu.stanford.nlp.math.ArrayMath;
import edu.stanford.nlp.objectbank.ObjectBank;
import edu.stanford.nlp.optimization.*;
import edu.stanford.nlp.optimization.Function;
import edu.stanford.nlp.sequences.*;
import edu.stanford.nlp.stats.ClassicCounter;
import edu.stanford.nlp.stats.Counter;
import edu.stanford.nlp.stats.TwoDimensionalCounter;
import edu.stanford.nlp.util.*;
import edu.stanford.nlp.util.logging.Redwood;

import java.io.*;
import java.lang.reflect.InvocationTargetException;
import java.text.DecimalFormat;
import java.text.NumberFormat;
import java.util.*;
import java.util.regex.*;
import java.util.stream.Collectors;
import java.util.zip.GZIPOutputStream;

import com.sun.net.httpserver.Headers;

import java.io.IOException;
import java.io.OutputStream;
import java.net.InetSocketAddress;

import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpHandler;
import com.sun.net.httpserver.HttpServer;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.InputStream;

import java.io.InputStreamReader;

import java.io.OutputStreamWriter;
import java.io.PrintWriter;

import java.nio.charset.Charset;
import java.nio.charset.StandardCharsets;

import java.nio.file.Files;
import java.nio.file.Path;

import java.nio.file.Paths;

import java.security.SecureRandom;

import java.text.DateFormat;
import java.text.SimpleDateFormat;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

import java.util.logging.FileHandler;
import java.util.zip.GZIPOutputStream;

public class ner_server {
    
    public static int PORT=8022;
    
    public static final  SecureRandom random = new SecureRandom();

    public static void main(String[] args) throws Exception {
//    StringUtils.logInvocationString(log, args);

    Properties props = StringUtils.argsToProperties(args);
    SeqClassifierFlags flags = new SeqClassifierFlags(props);
    CRFClassifier<CoreLabel> crf = CRFClassifier.chooseCRFClassifier(flags);
    String testFile = flags.testFile;
    String testFiles = flags.testFiles;
    String textFile = flags.textFile;
    String textFiles = flags.textFiles;
    String loadPath = flags.loadClassifier;
    String loadTextPath = flags.loadTextClassifier;
    String serializeTo = flags.serializeTo;
    String serializeToText = flags.serializeToText;

    if (crf.flags.useEmbedding && crf.flags.embeddingWords != null && crf.flags.embeddingVectors != null) {
      crf.readEmbeddingsData();
    }

    if (crf.flags.loadClassIndexFrom != null) {
      crf.classIndex = crf.loadClassIndexFromFile(crf.flags.loadClassIndexFrom);
    }

    if (loadPath != null) {
      crf.loadClassifierNoExceptions(loadPath, props);
    } else {
      crf.loadDefaultClassifier();
    }

    crf.loadTagIndex();




        HttpServer server = HttpServer.create(new InetSocketAddress("127.0.0.1",PORT), 0);
        server.createContext("/test", new TESTHandler());
//        server.createContext("/models.json", new ModelsJSONHandler());
        server.createContext("/", new FileHandler("html/index.html"));
        server.createContext("/ner", new NERHandler("logs",crf));
        
        // Add all files with known extenstion from html folder
        File folderFile = new File("html");
        File[] listOfFiles = folderFile.listFiles();

        for (int i = 0; i < listOfFiles.length; i++) {
            if (listOfFiles[i].isFile()) {
                String fname=listOfFiles[i].getName();
                if(fname.endsWith(".html") || fname.endsWith(".js") || fname.endsWith(".gif") || fname.endsWith(".css") || fname.endsWith(".jpg") || fname.endsWith(".jpeg")){
                    server.createContext("/"+fname, new FileHandler("html/"+fname));
                }
            }
        }
        
        System.out.println("Starting server on port "+PORT);
        server.start();
    }
    
    public static HashMap<String,String> getRequestParams(InputStream input) throws IOException {
        HashMap<String,String> params=new HashMap<String,String>(10);
        Integer state=0;
        String separator=""; 
        String currentName="";
        String currentContent="";

        BufferedReader in=new BufferedReader(new InputStreamReader(input));
        for(String line=in.readLine();line!=null && state!=100;line=in.readLine()){
            switch(state){
            case 0:
                if(line.length()>0){
                    separator=line;
                    state=1;
                }
                break;
            case 1:
                if(line.startsWith("Content-Disposition: form-data")){
                    int pos=line.indexOf("name=\"");
                    if(pos>0){
                        currentName=line.substring(pos+6);
                        pos=currentName.indexOf('"');
                        if(pos>0){
                            currentName=currentName.substring(0,pos);
                            state=2;
                        }
                    }
                }
                break;
            case 2:
                if(line.length()==0)state=3;
                break;
            case 3:
                if(line.startsWith(separator)){
                    params.put(currentName, currentContent);
                    currentName="";
                    currentContent="";
                    state=1;
                    if(line.equals(separator+"--"))state=100;
                }else{
                    if(currentContent.length()>0)currentContent+="\n";
                    currentContent+=line;
                }
                break;
            }
            //stringBuilder.append(line + "\n");
        };
        in.close();
        
        return params;
    }

    static class TESTHandler implements HttpHandler {
        @Override
        public void handle(HttpExchange ex) throws IOException {
            String response = "This is the received request:\n";
            
            HashMap<String,String> params=getRequestParams(ex.getRequestBody());

            for(Map.Entry<String, String> entry : params.entrySet()) {
                String key = entry.getKey();
                String value = entry.getValue();

                response+=key+"="+value+"\n";
            }
            
            Headers headers=ex.getResponseHeaders();
            headers.add("Content-type", "text/plain; charset=utf-8");
            
            ex.sendResponseHeaders(200, response.getBytes(StandardCharsets.UTF_8).length);
            
            OutputStream os = ex.getResponseBody();
            os.write(response.getBytes(StandardCharsets.UTF_8));
            os.close();
            
            ex.close();
        }
    }

/*    static class ModelsJSONHandler implements HttpHandler {
        @Override
        public void handle(HttpExchange ex) throws IOException {
            Headers headers=ex.getResponseHeaders();
            headers.add("Content-type", "application/json");
            
            String response = "[";
            boolean first=true;
            for(Map.Entry ent:sess.entrySet()){
                if(first)first=false;
                else response+=",";
                response+="\""+ent.getKey()+"\"";
            }
            response+="]";
            
            ex.sendResponseHeaders(200, response.getBytes(StandardCharsets.UTF_8).length);
            
            OutputStream os = ex.getResponseBody();
            os.write(response.getBytes(StandardCharsets.UTF_8));
            os.close();
            
            ex.close();
        }
    }
*/

    static class FileHandler implements HttpHandler {
        public byte[] content=null;
        public String fname=null;
        
        public FileHandler(String fname) throws FileNotFoundException, IOException {
            this.fname=fname;
            Path path = Paths.get(fname);
            content = Files.readAllBytes(path);
        }
        
        @Override
        public void handle(HttpExchange ex) throws IOException {
            //System.out.println("Request for: "+ex.getRequestURI().toString()+" Serving: "+fname);
            
            Headers headers=ex.getResponseHeaders();
            
            if(fname.endsWith(".js"))
                headers.add("Content-type", "application/javascript");
            else if(fname.endsWith(".gif"))
                headers.add("Content-type", "image/gif");
            else
                headers.add("Content-type", "text/html; charset=utf-8");
            
            ex.sendResponseHeaders(200, content.length);
            
            OutputStream os = ex.getResponseBody();
            os.write(content);
            os.close();
            
            ex.close();
        }
    }
    
    static class NERHandler implements HttpHandler {
        public String base_log_path;
        public CRFClassifier<CoreLabel> crf;
        //public Rules rulesPost=null;
        
        public NERHandler(String base_log_path,CRFClassifier<CoreLabel> crf){
            this.base_log_path=base_log_path;
	    this.crf=crf;
//            try{this.rulesPost=new Rules("model_ner/postprocess.json");}catch(Exception ex){;}
        }
        
        @Override
        public void handle(HttpExchange ex) throws IOException {
            try{
                HashMap<String,String> params=getRequestParams(ex.getRequestBody());
                if(!params.containsKey("text"))return ;
                
                String text=params.get("text");
                boolean debug=false;
                if(params.containsKey("debug"))debug=Boolean.parseBoolean(params.get("debug"));

    		DocumentReaderAndWriter<CoreLabel> readerAndWriter = crf.defaultReaderAndWriter();
		ObjectBank<List<CoreLabel>> list=crf.makeObjectBankFromString(text, readerAndWriter);
                ByteArrayOutputStream baos=new ByteArrayOutputStream();
                PrintWriter out = new PrintWriter(new BufferedWriter(new OutputStreamWriter(baos,StandardCharsets.UTF_8)));
		crf.classifyAndWriteAnswers(list, out, readerAndWriter, true);

                out.close();

                String ip=ex.getRemoteAddress().getAddress().toString();
                ip=ip.substring(ip.indexOf('/')+1);
                
                DateFormat dateFormat = new SimpleDateFormat("yyyy");
                Date date = new Date();
                String year=dateFormat.format(date);
                dateFormat = new SimpleDateFormat("MM");
                String month=dateFormat.format(date);
                dateFormat = new SimpleDateFormat("dd");
                String day=dateFormat.format(date);
                dateFormat=new SimpleDateFormat("HHmmss");
                String time=dateFormat.format(date)+"_"+random.nextInt();
                String path=this.base_log_path+"/"+year+"/"+month+"/"+day;
                File log_path=new File(path);
                log_path.mkdirs();
                
                String response=new String(baos.toByteArray(),StandardCharsets.UTF_8);
    
                PrintWriter outLog = new PrintWriter(new BufferedWriter(new OutputStreamWriter(new FileOutputStream(path+"/"+ip+"_"+time+".txt"),StandardCharsets.UTF_8)));
                Headers reqh=ex.getRequestHeaders();
                for(Map.Entry<String,List<String>> entry:reqh.entrySet()){
                    for(String s:entry.getValue()){
                        outLog.println(entry.getKey()+":"+s);
                    }
                }
                outLog.println("\n--------------------------------------------------------------------------------\n");
                outLog.println(params.get("text"));
                outLog.println("\n--------------------------------------------------------------------------------\n");
                outLog.println(response);
                outLog.close();
                
                Headers headers=ex.getResponseHeaders();
                headers.add("Content-type", "text/plain; charset=utf-8");
                
                ex.sendResponseHeaders(200, response.getBytes(StandardCharsets.UTF_8).length);
                
                OutputStream os = ex.getResponseBody();
                os.write(response.getBytes(StandardCharsets.UTF_8));
                os.close();
                
                ex.close();
            }catch(Exception exception){
                System.out.println("Exception while handling request:");
                exception.printStackTrace();

                ex.sendResponseHeaders(500, 0);
                
                OutputStream os = ex.getResponseBody();
                os.close();

            }
                
        }
    }
    


}
